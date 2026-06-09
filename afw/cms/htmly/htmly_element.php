<?php
class HtmlyElement
{
    private $balise = "";
    private $text_direction = '';
    private $elements = [];
    private $container = true;
    private $attributes = [];

    /**
     * @var array<string>
     */
    private $classes = [];

    protected $id = "";
    protected $name = "";


    /**
     * @param string $balise
     */

    public function __construct(
        $balise,
        $container = true,
        $id = "",
        $name = "",
        $text_direction = ''
    ) {
        $this->balise = $balise;
        $this->container = $container;
        $this->name = $name;
        $this->id = $id;
        $this->text_direction = $text_direction;
    }

    /**
     * @param string $classCss
     */

    public function addClass($classCss)
    {
        $this->classes[$classCss] = $classCss;
    }

    /**
     * @param string $class
     */
    public function removeClass($class)
    {
        unset($this->classes[$class]);
    }

    public function addElement(mixed $element)
    {
        if (!$this->container) throw new AfwRuntimeException("Can not add element into a non container component");
        $this->elements[] = $element;
    }


    /**
     * @param string $attribute
     * @param string $value
     */

    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * @param string $attribute
     */
    public function removeAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    /**
     * @return string
     */
    private function myPureHtmlStart()
    {
        $html = "<" . $this->balise;

        if ($this->name) $html .= " name='" . $this->name . "'";
        if ($this->id) $html .= " id='" . $this->id . "'";
        if ($this->text_direction) $html .= " dir='" . $this->text_direction . "'";

        $css_class = implode(" ", $this->classes);
        if ($css_class) $html .= " class='" . $css_class . "'";

        /*
        foreach($this->attributes as $attribute => $value) {
            $value_cleaned = str_replace("\"", "'", $value);
            $html .= " $attribute=\"$value_cleaned\"";
        }*/

        $html .= $this->renderAttributes();

        $html .= ">";

        return $html;
    }

    /**
     * @return string
     */
    private function myPureHtmlEnd()
    {
        $html = "</" . $this->balise . ">";
        return $html;
    }

    protected function renderAttributes()
    {
        if (empty($this->attributes) || !is_array($this->attributes)) {
            return '';
        }

        $rendered = '';
        foreach ($this->attributes as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $escaped = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $rendered .= ' ' . $name . '="' . $escaped . '"';
        }

        return $rendered;
    }

    /**
     * @return string
     */

    protected function renderSpecialHtml()
    {
        return "";
    }

    /**
     * @return string
     */

    protected function renderSpecialHtmlSuffix()
    {
        return "";
    }

    /**
     * @param HtmlyElement $element
     * @return string
     */
    protected function beforeRenderElement($element)
    {
        return "";
    }

    /**
     * @param HtmlyElement $element
     * @return string
     */
    protected function afterRenderElement($element)
    {
        return "";
    }

    /**
     * @return string
     */
    protected function afterRenderElements()
    {
        return "";
    }

    /**
     * @return string
     */

    public final function renderHtml()
    {
        $html_content = $this->renderSpecialHtml();
        if ($this->container) {
            foreach ($this->elements as $element) {
                if (is_string($element) or is_integer($element) or is_float($element) or is_bool($element)) {
                    $html_content .= $element;
                } elseif (is_object($element) && method_exists($element, 'renderHtml')) {
                    $html_content .= $this->beforeRenderElement($element);
                    $html_content .= $element->renderHtml();
                    $html_content .= $this->afterRenderElement($element);
                } else {
                    throw new AfwRuntimeException("Strange html element will be added : " . var_export($element, true));
                }
            }
            $html_content .= $this->afterRenderElements();
        }
        return $this->myPureHtmlStart() . $html_content . $this->myPureHtmlEnd() . $this->renderSpecialHtmlSuffix();
    }
}
