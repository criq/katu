<?php

namespace Katu\Config\ThirdParty\Google;

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Katu\Cache\General as SharedCache;
use Katu\Files\File;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

/**
 * Google Cloud Secret Manager has no client-side caching in the PHP SDK, so without memoization
 * every getSecret() does a full round-trip to secretmanager.googleapis.com (~150–200 ms) plus the
 * cost of rebuilding the gRPC client / re-reading the service-account JSON / re-resolving the
 * project id whenever a caller does `new SecretManagerConfig`.
 *
 * Hot consumers (e.g. Katu\Types\Encryption\TEncryptedString::encrypt() / generateIv() invoked by
 * Package::__toString() during URL building) hit getSecret() hundreds of times per request, so
 * the call path is layered:
 *
 *   - L0 (always on): service-account file handle, project id, and gRPC client are static for
 *     the full process lifetime — they're derived from the local key file, not from GSM, so they
 *     never need to refresh mid-process.
 *   - L1 (per process): static $secretMemo array, ~ns hits within the worker.
 *   - L2 (cross-worker): Katu\Cache\General → Katu\Cache\Adapters\Redis → App\Classes\Redis
 *     (Predis). One cold worker pays the GSM cost; every other worker on the host sees a Redis
 *     hit (~1 ms) until the SECRET_MEMO_TTL_SECONDS window expires.
 *   - L3 (authoritative): GSM accessSecretVersion. Results write through L2 and L1.
 *
 * Failures: GSM exceptions are caught here; we evict the L1 entry, log, and return null. We never
 * cache null in L2 (the underlying General cache only writes a value when the callback returns).
 * Redis failures inside L2 fall through to GSM transparently (Adapters\Redis::isSupported()
 * returns false, the chain skips it).
 *
 * Security trade-off: ENCRYPTION_KEY / ENCRYPTION_SALT and other secrets live in Redis (under the
 * env-namespaced TIdentifier key) for up to SECRET_MEMO_TTL_SECONDS at a time. The stack binds
 * Redis to 127.0.0.1 / the Docker network (AGENTS.md §1.2), so the practical trust boundary
 * already includes anyone with shell on the PHP host. If Redis ever gets exposed externally, or
 * AOF/RDB dumps start landing in backups/log shipping, revisit this layer.
 *
 * setSecret() invalidates L1 (all versions of the secret) and clears the L2 "latest" entry
 * immediately. Explicit-version entries are immutable; their TTL retires them on its own.
 */
class SecretManagerConfig extends \Katu\Config\Config
{
	const SECRET_MEMO_TTL_SECONDS = 60;

	/** @var File|null */
	private static $serviceAccountFile;

	/** @var string|null */
	private static $projectId;

	/** @var SecretManagerServiceClient|null */
	private static $client;

	/** @var array<string, array{value: string, expiresAt: int}> "name@version" => entry (successful reads only) */
	private static $secretMemo = [];

	public function getServiceAccountFile(): File
	{
		if (!self::$serviceAccountFile) {
			self::$serviceAccountFile = new File(\App\App::getBaseDir(), \App\App::getEnvConfig()->getVariable("SECRET_MANAGER_KEY_FILE"));
		}

		return self::$serviceAccountFile;
	}

	public function getProjectId(): string
	{
		if (!self::$projectId) {
			self::$projectId = \Katu\Files\Formats\JSON::decodeAsArray($this->getServiceAccountFile()->get())["project_id"];
		}

		return self::$projectId;
	}

	public function getClient(): SecretManagerServiceClient
	{
		if (!self::$client) {
			self::$client = new SecretManagerServiceClient([
				"credentials" => $this->getServiceAccountFile()->getPath(),
			]);
		}

		return self::$client;
	}

	public function getSecret(string $name, string $version = "latest"): ?string
	{
		$memoKey = $name . "@" . $version;
		$now = time();

		if (isset(self::$secretMemo[$memoKey]) && self::$secretMemo[$memoKey]["expiresAt"] > $now) {
			return self::$secretMemo[$memoKey]["value"];
		}

		try {
			$value = SharedCache::get(
				static::buildCacheIdentifier($name, $version),
				static::buildCacheTimeout(),
				function () use ($name, $version) {
					$client = $this->getClient();
					$versionName = $client->secretVersionName($this->getProjectId(), $name, $version);
					$response = $client->accessSecretVersion($versionName);

					return $response->getPayload()->getData();
				}
			);

			if (!is_string($value) || $value === "") {
				unset(self::$secretMemo[$memoKey]);

				return null;
			}

			self::$secretMemo[$memoKey] = [
				"value" => $value,
				"expiresAt" => $now + static::SECRET_MEMO_TTL_SECONDS,
			];

			return $value;
		} catch (\Exception $e) {
			unset(self::$secretMemo[$memoKey]);
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return null;
		}
	}

	public function setSecret(string $name, string $value): string
	{
		foreach (array_keys(self::$secretMemo) as $memoKey) {
			if (strpos($memoKey, $name . "@") === 0) {
				unset(self::$secretMemo[$memoKey]);
			}
		}

		try {
			(new SharedCache(static::buildCacheIdentifier($name, "latest"), static::buildCacheTimeout()))->clear();
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__, "shared-cache-clear"))->error($e);
		}

		$secretName = $this->getClient()->secretName($this->getProjectId(), $name);

		try {
			$this->getClient()->getSecret($secretName);
		} catch (\Google\ApiCore\ApiException $e) {
			if ((\Katu\Files\Formats\JSON::decodeAsArray($e->getMessage())["status"] ?? null) == "NOT_FOUND") {
				$parent = "projects/{$this->getProjectId()}";
				$secretId = $name;

				$secret = new \Google\Cloud\SecretManager\V1\Secret;
				$secret->setReplication(new \Google\Cloud\SecretManager\V1\Replication([
					"automatic" => new \Google\Cloud\SecretManager\V1\Replication\Automatic(),
				]));

				$this->getClient()->createSecret($parent, $secretId, $secret);
			} else {
				throw $e;
			}
		}

		return $this->getClient()->addSecretVersion($secretName, new SecretPayload([
			"data" => $value,
		]))->getName();
	}

	private static function buildCacheIdentifier(string $name, string $version): TIdentifier
	{
		return new TIdentifier(__CLASS__, "getSecret", $name, $version);
	}

	private static function buildCacheTimeout(): Timeout
	{
		return new Timeout(static::SECRET_MEMO_TTL_SECONDS . " seconds");
	}
}
