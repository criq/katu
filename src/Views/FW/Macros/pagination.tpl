{% macro getDefault(url, pagination, options) %}

	{% set prevCopy      = options.prevCopy|default("Previous") %}
	{% set nextCopy      = options.nextCopy|default("Next") %}
	{% set pageIdent     = options.pageIdent|default("page") %}

	{% set pages = getPages(pagination, { allPagesLimit: options.allPagesLimit, endsOffset: options.endsOffset, currentOffset: options.currentOffset }) %}

	{% if pages|length > 1 %}
		<p class="pagination">

			{% if pagination.page > 1 %}
				<a href="">{{ prevCopy }}</a>
			{% else %}
				{{ prevCopy }}
			{% endif %}

			{% for key, page in pages %}

				{% if page != pagination.page %}
					<a href="">{{ page }}</a>
				{% else %}
					{{ page }}
				{% endif %}

				{% if page + 1 != pages[key + 1] and page + 1 <= pagination.getMaxPage %}
					&hellip;
				{% endif %}

			{% endfor %}

			{% if pagination.page < pagination.getMaxPage %}
				<a href="">{{ nextCopy }}</a>
			{% else %}
				{{ nextCopy }}
			{% endif %}

		</p>
	{% endif %}

{% endmacro %}