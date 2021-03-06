<?php

/*
	Small template engine
	
	You can provide a template in the construct.
	A template is a file which first line list all tags separated by '|' and the rest being valid HTML with 
	tag to be replaced (see defaultStyle.template), by default tag are replaced by the empty string
	A template must have a HTML Header tag ##HtmlHeader##, a default value is hardcoded but can be overridden
	
	You can set tags value with setContent($tag, $value)
	
	You can add a style to the header with addStyle($style)
	
	You can render a page with the render() method	
*/
class TemplateEngine {

	protected $content;  //array
	protected $template; //string
	
	private $defaultHtmlHeader = <<<HtmlHeader
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Team2 - Secret Chat</title>
	<link href="/css/bootstrap.min.css" rel="stylesheet">
	<link href="/css/main_v2.css" rel="stylesheet">
</head>
HtmlHeader;

	public function __construct($templatePath = "templates/defaultStyle.template") {
		if(!file_exists($templatePath)) 
			throw new InvalidArgumentException("No template file");
		
		$this->content = array();
		$this->setTemplate($templatePath);
		if(!isset($this->content["##HtmlHeader##"])) 
			throw new InvalidArgumentException("Template has no HtmlHeader tag");
		$this->content["##HtmlHeader##"] = $this->defaultHtmlHeader;
	}

	public function setContent($tag, $value) {
		if(!isset($this->content[$tag])) 
			throw new InvalidArgumentException("The tag $tag doesn't exist in this template");
		$this->content[$tag] = $value;
	}
	
	//Add a style in the header
	public function addStyle($style) {
		$match = array();
		$HtmlHeader = $this->content["##HtmlHeader##"];
		if(preg_match('#(.*<style>.*)(</style>.*)#Usi', $HtmlHeader, $match)) {
			$HtmlHeader = $match[1].' '.$style.$match[2];
		} else {
			$match = array();
			if(!preg_match('#(.*<head>.*)(</head>.*)#Usi', $HtmlHeader, $match))
				throw new InvalidArgumentException('Invalid HtmlHeader');
			$HtmlHeader = $match[1]."\n<style>".$style."</style>\n".$match[2];
		}
		$this->content["##HtmlHeader##"] = $HtmlHeader;
	}
	
	public function render() {
		$page = $this->template;
		foreach($this->content as $key=>$value) {
			$page = str_replace($key, $value, $page);
		}
		
		echo $page;
	}
	
	private function setTemplate($templatePath) {
		$rawTemplate = file_get_contents($templatePath);
		$firstLineLength = strpos($rawTemplate,"\n");
		$tags = explode("|", substr($rawTemplate, 0, $firstLineLength-1));
		foreach($tags as $value) {
			$this->content[$value] = "";
		}
		$this->template = substr($rawTemplate, $firstLineLength+1);
	}
}

?>