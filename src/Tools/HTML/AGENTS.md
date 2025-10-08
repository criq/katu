# HTML Generation System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the HTML Generation system in the KATU framework. The HTML system provides programmatic HTML generation with a rich set of elements, attributes, and collections.

---

## 1. System Overview

### 1.1. Purpose
- **Programmatic HTML Generation:** Create HTML elements programmatically
- **Element Management:** Rich set of pre-built HTML elements
- **Attribute Handling:** Comprehensive attribute management system
- **Collection Support:** HTML collections for managing multiple elements
- **Stream Integration:** PSR-7 stream interface support
- **Twig Integration:** Seamless integration with Twig templating

### 1.2. Architecture
- **Core Classes:** `HTML`, `ElementNode`, `Attribute`, `Node`
- **Element System:** Pre-built HTML elements with specific functionality
- **Collection System:** Collections for attributes, nodes, and elements
- **Interface System:** Common interfaces for HTML components
- **Stream Support:** PSR-7 stream interface integration

---

## 2. Core HTML Classes

### 2.1. HTML (`Katu\Tools\HTML\HTML`)
**Location:** `HTML.php`

Main HTML container class:

```php
// Key methods:
public function __construct(string $html)
public function setHTML(string $html): HTML
public function getHTML(): string
public function getStream(): StreamInterface
public function getTwigMarkup(): \Twig\Markup
```

**Key Features:**
- HTML string management
- PSR-7 stream interface
- Twig markup integration
- String conversion support

### 2.2. ElementNode (`Katu\Tools\HTML\ElementNode`)
**Location:** `ElementNode.php`

Base class for HTML elements:

```php
// Key methods:
public function __construct(string $name, ?AttributeCollection $attributes = null, ?NodeCollection $nodes = null)
public function setName(string $name): ElementNode
public function getName(): string
public function setAttributes(?AttributeCollection $attributes): ElementNode
public function getAttributes(): AttributeCollection
public function setNodes(?NodeCollection $nodes): ElementNode
public function getNodes(): NodeCollection
public function getHTML(): HTML
public function getIsPairElement(): bool
```

**Key Features:**
- Element name management
- Attribute collection support
- Child node management
- Self-closing element detection
- HTML generation

### 2.3. Attribute (`Katu\Tools\HTML\Attribute`)
**Location:** `Attribute.php`

HTML attribute management:

```php
// Key methods:
public function __construct(string $name, ?string $value = null)
public function setName(string $name): Attribute
public function getName(): string
public function setValue(?string $value): Attribute
public function getValue(): ?string
public function getHTML(): HTML
```

**Key Features:**
- Name-value pair management
- HTML attribute generation
- Value validation and escaping

### 2.4. Node (`Katu\Tools\HTML\Node`)
**Location:** `Node.php`

Abstract base class for HTML nodes:

```php
// Key methods:
abstract public function getHTML(): HTML
public function getIsTextNode(): bool
public function getIsElementNode(): bool
```

---

## 3. HTML Elements

### 3.1. Pre-built Elements (`Elements/`)

#### FormElement (`Elements/FormElement.php`)
Form element with method and action:

```php
$form = new FormElement("POST", "/submit");
$form->setMethod("POST");
$form->setAction("/submit");
```

#### InputElement (`Elements/InputElement.php`)
Input element with type and attributes:

```php
$input = new InputElement("text", "username");
$input->setPlaceholder("Enter username");
$input->setRequired(true);
```

#### SelectElement (`Elements/SelectElement.php`)
Select element with options:

```php
$select = new SelectElement("country");
$select->addOption(new OptionElement("us", "United States"));
$select->addOption(new OptionElement("ca", "Canada"));
```

#### TextareaElement (`Elements/TextareaElement.php`)
Textarea element:

```php
$textarea = new TextareaElement("message");
$textarea->setRows(5);
$textarea->setCols(50);
$textarea->setPlaceholder("Enter your message");
```

#### DivElement (`Elements/DivElement.php`)
Div element with classes and content:

```php
$div = new DivElement("container");
$div->addClass("row");
$div->addClass("justify-content-center");
```

#### AElement (`Elements/AElement.php`)
Anchor element with href and text:

```php
$link = new AElement("https://example.com", "Click here");
$link->setTarget("_blank");
$link->addClass("btn");
```

#### H1Element (`Elements/H1Element.php`)
Heading element:

```php
$heading = new H1Element("Welcome to Our Site");
$heading->addClass("main-title");
```

#### LabelElement (`Elements/LabelElement.php`)
Label element:

```php
$label = new LabelElement("username", "Username:");
$label->setFor("username-input");
```

#### UlElement (`Elements/UlElement.php`)
Unordered list element:

```php
$ul = new UlElement();
$ul->addClass("nav");
$ul->addItem(new LiElement("Home"));
$ul->addItem(new LiElement("About"));
```

#### LiElement (`Elements/LiElement.php`)
List item element:

```php
$li = new LiElement("List item content");
$li->addClass("active");
```

#### NavElement (`Elements/NavElement.php`)
Navigation element:

```php
$nav = new NavElement();
$nav->addClass("main-nav");
```

#### PElement (`Elements/PElement.php`)
Paragraph element:

```php
$p = new PElement("This is a paragraph.");
$p->addClass("lead");
```

#### OptionElement (`Elements/OptionElement.php`)
Option element for selects:

```php
$option = new OptionElement("value", "Display Text");
$option->setSelected(true);
```

---

## 4. Collections

### 4.1. AttributeCollection (`AttributeCollection.php`)
**Location:** `AttributeCollection.php`

Collection for managing HTML attributes:

```php
// Key methods:
public function addAttribute(Attribute $attribute): AttributeCollection
public function getHTML(): HTML
public function getAttribute(string $name): ?Attribute
public function hasAttribute(string $name): bool
public function removeAttribute(string $name): AttributeCollection
```

### 4.2. NodeCollection (`NodeCollection.php`)
**Location:** `NodeCollection.php`

Collection for managing HTML nodes:

```php
// Key methods:
public function addNode(Node $node): NodeCollection
public function getHTML(): HTML
public function getNodes(): array
```

### 4.3. Classes (`Classes/`)
**Location:** `Classes/`

Specialized class collections:

#### SelectOptionCollection (`Classes/SelectOptionCollection.php`)
Collection for select options:

```php
$options = new SelectOptionCollection();
$options[] = new OptionElement("value1", "Text 1");
$options[] = new OptionElement("value2", "Text 2");
```

---

## 5. Usage Patterns

### 5.1. Basic HTML Generation
```php
use Katu\Tools\HTML\HTML;
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\AttributeCollection;

// Create simple HTML
$html = new HTML("<h1>Hello World</h1>");
echo $html; // Outputs: <h1>Hello World</h1>

// Create element with attributes
$attributes = new AttributeCollection();
$attributes->addAttribute(new Attribute("class", "container"));
$attributes->addAttribute(new Attribute("id", "main"));

$div = new ElementNode("div", $attributes);
echo $div; // Outputs: <div class="container" id="main"></div>
```

### 5.2. Form Generation
```php
use Katu\Tools\HTML\Elements\FormElement;
use Katu\Tools\HTML\Elements\InputElement;
use Katu\Tools\HTML\Elements\LabelElement;
use Katu\Tools\HTML\NodeCollection;

// Create form
$form = new FormElement("POST", "/submit");
$form->addClass("user-form");

// Create form fields
$nodes = new NodeCollection();

// Username field
$usernameLabel = new LabelElement("username", "Username:");
$usernameInput = new InputElement("text", "username");
$usernameInput->setRequired(true);
$usernameInput->setPlaceholder("Enter username");

$nodes->addNode($usernameLabel);
$nodes->addNode($usernameInput);

// Email field
$emailLabel = new LabelElement("email", "Email:");
$emailInput = new InputElement("email", "email");
$emailInput->setRequired(true);

$nodes->addNode($emailLabel);
$nodes->addNode($emailInput);

// Submit button
$submitInput = new InputElement("submit", "submit");
$submitInput->setValue("Submit");

$nodes->addNode($submitInput);

$form->setNodes($nodes);

echo $form; // Outputs complete form HTML
```

### 5.3. Navigation Menu
```php
use Katu\Tools\HTML\Elements\NavElement;
use Katu\Tools\HTML\Elements\UlElement;
use Katu\Tools\HTML\Elements\LiElement;
use Katu\Tools\HTML\Elements\AElement;

// Create navigation
$nav = new NavElement();
$nav->addClass("main-nav");

// Create menu list
$ul = new UlElement();
$ul->addClass("nav-list");

// Add menu items
$homeLink = new AElement("/", "Home");
$homeLi = new LiElement();
$homeLi->setNodes(new NodeCollection([$homeLink]));
$ul->addNode($homeLi);

$aboutLink = new AElement("/about", "About");
$aboutLi = new LiElement();
$aboutLi->setNodes(new NodeCollection([$aboutLink]));
$ul->addNode($aboutLi);

$contactLink = new AElement("/contact", "Contact");
$contactLi = new LiElement();
$contactLi->setNodes(new NodeCollection([$contactLink]));
$ul->addNode($contactLi);

$nav->setNodes(new NodeCollection([$ul]));

echo $nav; // Outputs complete navigation HTML
```

### 5.4. Table Generation
```php
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\AttributeCollection;
use Katu\Tools\HTML\Attribute;

// Create table
$tableAttributes = new AttributeCollection();
$tableAttributes->addAttribute(new Attribute("class", "data-table"));
$tableAttributes->addAttribute(new Attribute("border", "1"));

$table = new ElementNode("table", $tableAttributes);

// Create table header
$thead = new ElementNode("thead");
$tr = new ElementNode("tr");
$th1 = new ElementNode("th", null, new NodeCollection([new TextNode("Name")]));
$th2 = new ElementNode("th", null, new NodeCollection([new TextNode("Email")]));
$th3 = new ElementNode("th", null, new NodeCollection([new TextNode("Action")]));

$tr->setNodes(new NodeCollection([$th1, $th2, $th3]));
$thead->setNodes(new NodeCollection([$tr]));

// Create table body
$tbody = new ElementNode("tbody");
// Add rows...

$table->setNodes(new NodeCollection([$thead, $tbody]));

echo $table; // Outputs complete table HTML
```

### 5.5. Dynamic Content Generation
```php
function generateUserCard($user) {
    $cardDiv = new DivElement();
    $cardDiv->addClass("user-card");

    // User name
    $nameH3 = new H1Element($user['name']);
    $nameH3->addClass("user-name");

    // User email
    $emailP = new PElement($user['email']);
    $emailP->addClass("user-email");

    // Edit link
    $editLink = new AElement("/users/{$user['id']}/edit", "Edit");
    $editLink->addClass("btn");
    $editLink->addClass("btn-primary");

    $cardDiv->setNodes(new NodeCollection([
        $nameH3,
        $emailP,
        $editLink
    ]));

    return $cardDiv;
}

// Usage
$user = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
$card = generateUserCard($user);
echo $card;
```

---

## 6. Advanced Features

### 6.1. Stream Integration
```php
use Psr\Http\Message\ResponseInterface;

// Create HTML
$html = new HTML("<h1>Stream Response</h1>");

// Get stream
$stream = $html->getStream();

// Use in PSR-7 response
$response = $response->withBody($stream);
```

### 6.2. Twig Integration
```php
// Create HTML element
$div = new DivElement("Hello World");
$div->addClass("greeting");

// Get Twig markup
$markup = $div->getHTML()->getTwigMarkup();

// Use in Twig template
// {{ markup|raw }}
```

### 6.3. Custom Element Creation
```php
class CustomButtonElement extends ElementNode
{
    public function __construct(string $text, string $type = "button", ?string $onclick = null)
    {
        $attributes = new AttributeCollection();
        $attributes->addAttribute(new Attribute("type", $type));
        $attributes->addAttribute(new Attribute("class", "btn btn-primary"));

        if ($onclick) {
            $attributes->addAttribute(new Attribute("onclick", $onclick));
        }

        $nodes = new NodeCollection([new TextNode($text)]);

        parent::__construct("button", $attributes, $nodes);
    }
}

// Usage
$button = new CustomButtonElement("Click Me", "button", "alert('Hello!')");
echo $button; // Outputs: <button type="button" class="btn btn-primary" onclick="alert('Hello!')">Click Me</button>
```

### 6.4. Conditional Rendering
```php
function renderConditionalContent($showDetails, $user) {
    $container = new DivElement();
    $container->addClass("user-info");

    // Always show name
    $name = new H1Element($user['name']);
    $container->addNode($name);

    // Conditionally show details
    if ($showDetails) {
        $email = new PElement("Email: " . $user['email']);
        $phone = new PElement("Phone: " . $user['phone']);

        $container->addNode($email);
        $container->addNode($phone);
    }

    return $container;
}
```

---

## 7. Best Practices

### 7.1. Element Organization
- Use appropriate element types for content
- Group related elements in containers
- Use semantic HTML elements when possible

### 7.2. Attribute Management
- Use descriptive class names
- Group related attributes together
- Validate attribute values

### 7.3. Performance
- Reuse element instances when possible
- Use collections for multiple similar elements
- Consider caching for complex structures

### 7.4. Security
- Escape user input in text content
- Validate attribute values
- Use proper HTML encoding

---

## 8. Integration Examples

### 8.1. Controller Integration
```php
class FormController extends Controller
{
    public function renderForm(ServerRequestInterface $request): ResponseInterface
    {
        $form = $this->createUserForm();
        $html = $form->getHTML();

        return $this->htmlResponse($html->getHTML());
    }

    private function createUserForm(): FormElement
    {
        $form = new FormElement("POST", "/users/create");
        $form->addClass("user-form");

        // Add form fields...

        return $form;
    }
}
```

### 8.2. Template Integration
```php
// In Twig template
{% set form = controller.createUserForm() %}
{{ form.getHTML()|raw }}
```

### 8.3. API Response
```php
class HTMLController extends Controller
{
    public function getHTML(ServerRequestInterface $request): ResponseInterface
    {
        $element = $this->createElement();
        $html = $element->getHTML();

        return $response
            ->withHeader("Content-Type", "text/html")
            ->withBody($html->getStream());
    }
}
```

---

## 9. Common Patterns

### 9.1. Form Generation Pattern
```php
// Complete form generation
$form = new Form();
$form->setMethod("POST")
     ->setAction("/users/create")
     ->addAttribute("class", "user-form");

$form->addChild(new Input("name", "text", "Name"))
     ->addChild(new Input("email", "email", "Email"))
     ->addChild(new Input("password", "password", "Password"))
     ->addChild(new Button("Submit", "submit"));

$html = $form->getHTML();
```

### 9.2. Navigation Generation Pattern
```php
// Navigation menu generation
$nav = new Nav();
$nav->addAttribute("class", "main-navigation");

$homeLink = new A("Home", "/");
$aboutLink = new A("About", "/about");
$contactLink = new A("Contact", "/contact");

$nav->addChild($homeLink)
    ->addChild($aboutLink)
    ->addChild($contactLink);
```

### 9.3. Table Generation Pattern
```php
// Data table generation
$table = new Table();
$table->addAttribute("class", "data-table");

$header = new Tr();
$header->addChild(new Th("Name"))
       ->addChild(new Th("Email"))
       ->addChild(new Th("Actions"));

$table->addChild($header);

foreach ($users as $user) {
    $row = new Tr();
    $row->addChild(new Td($user->name))
        ->addChild(new Td($user->email))
        ->addChild(new Td(new A("Edit", "/users/{$user->id}/edit")));

    $table->addChild($row);
}
```

---

## 10. Troubleshooting

### 10.1. Common Issues
- **Empty Elements:** Check if nodes are properly added
- **Missing Attributes:** Verify attribute collection setup
- **Invalid HTML:** Check element nesting and structure
- **Stream Issues:** Ensure proper PSR-7 stream usage

### 10.2. Debugging
- Use `var_dump()` to inspect element structure
- Check attribute and node collections
- Validate HTML output manually
- Test with simple elements first

---

This documentation provides comprehensive coverage of the HTML Generation system. For specific implementation details, refer to the source code in `src/Tools/HTML/`.
