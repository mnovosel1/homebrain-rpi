<?php

/* network.twig */
class __TwigTemplate_3a1e3ec6653161a24a291b26f8c0b7723cd0808f152b14dc5acb18cc02ac7d30 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("layout_page.twig", "network.twig", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout_page.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 3
        $context["page"] = "network";
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 5
    public function block_title($context, array $blocks = array())
    {
        echo "GitList";
    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
        // line 8
        echo "    ";
        $this->loadTemplate("breadcrumb.twig", "network.twig", 8)->display(array_merge($context, array("breadcrumbs" => array(0 => array("dir" => "Network", "path" => "")))));
        // line 9
        echo "\t<div class=\"network-view\">
\t\t<div class=\"network-header\">
\t\t\t<div class=\"meta\">Network Graph of ";
        // line 11
        echo twig_escape_filter($this->env, ($context["repo"] ?? null), "html", null, true);
        echo " / ";
        echo twig_escape_filter($this->env, ($context["commitishPath"] ?? null), "html", null, true);
        echo "</div>
\t\t</div>

\t\t<div class=\"network-graph\" data-source=\"";
        // line 14
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("networkData", array("repo" => ($context["repo"] ?? null), "commitishPath" => ($context["commitishPath"] ?? null))), "html", null, true);
        echo "\">
\t\t";
        // line 16
        echo "
\t\t</div>
\t</div>

\t

    <hr />
";
    }

    public function getTemplateName()
    {
        return "network.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  60 => 16,  56 => 14,  48 => 11,  44 => 9,  41 => 8,  38 => 7,  32 => 5,  28 => 1,  26 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "network.twig", "/srv/HomeBrain/www/git/themes/default/twig/network.twig");
    }
}
