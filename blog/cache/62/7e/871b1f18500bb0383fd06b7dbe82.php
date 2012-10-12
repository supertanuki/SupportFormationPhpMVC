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
        if (isset($context["helloworld"])) { $_helloworld_ = $context["helloworld"]; } else { $_helloworld_ = null; }
        echo twig_escape_filter($this->env, $_helloworld_, "html", null, true);
        echo "
\t</body>
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
        return array (  27 => 9,  17 => 1,);
    }
}
