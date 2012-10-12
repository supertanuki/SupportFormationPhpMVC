<?php

/* commentaire/Commentaire.list.twig.html */
class __TwigTemplate_627e871b1f18500bb0383fd06b7dbe82 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<html>
\t<head>
\t\t<title>Mon blog</title>
\t</head>
\t<body>
\t\t<h1><a href=\"/\">Mon 1er blog PHP</a></h1>
\t\t<hr />
\t\t
\t\t";
        // line 9
        if (isset($context["comments"])) { $_comments_ = $context["comments"]; } else { $_comments_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_comments_);
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["commentaire"]) {
            // line 10
            echo "\t\t\t<li>
\t\t\t\t";
            // line 11
            if (isset($context["commentaire"])) { $_commentaire_ = $context["commentaire"]; } else { $_commentaire_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_commentaire_, "auteur"), "html", null, true);
            echo " :<br />
\t\t\t\t";
            // line 12
            if (isset($context["commentaire"])) { $_commentaire_ = $context["commentaire"]; } else { $_commentaire_ = null; }
            echo twig_escape_filter($this->env, $this->getAttribute($_commentaire_, "message"), "html", null, true);
            echo "
\t\t\t</li>
\t\t";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            // line 15
            echo "\t\t\tAucun commentaire n'a été trouvé
\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['commentaire'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 17
        echo "\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "commentaire/Commentaire.list.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  57 => 17,  50 => 15,  41 => 12,  36 => 11,  33 => 10,  27 => 9,  17 => 1,);
    }
}
