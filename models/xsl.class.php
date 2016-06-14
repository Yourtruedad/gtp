<?php
// tu klasa do obslugi dodawania tagow xsl

class xsl 
{
	public function loadModuleFile($name) {
		if (empty($name)) {
            $name = CONFIG_APP_404;
        }
        return './views/' . $name . '.php';
	}

    public function loadXslFile($name) {
        $xsl = new DOMDocument();
        if (empty($name)) {
            $name = CONFIG_APP_404;
        }
        $xsl->load('./views/' . $name . '.xsl');
        $xsl->formatOutput = true;
        return $xsl->saveHTML();
    }

    public function parseHtmlByElement($string, $queryType) {
        $dom = new DOMDocument();
        @$dom->loadHTML($string);
                            
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query($queryType);
        $rows = [];
            
        foreach($elements as $element) {
            $rows[] = $this->parseHtml($element);
        }
        return $rows;
    }

    public function parseHtml($node, $escape = false) {
        $innerHTML = '';

        $children = $node->childNodes;
        foreach ($children as $child) {
            $dom = new DOMDocument();
            $dom->appendChild($dom->importNode($child, true));
            $innerHTML .= ($escape ? htmlspecialchars($dom->saveHTML()) : $dom->saveHTML());
        }

       return trim($innerHTML) . "\r\n\r\n";
    }
}