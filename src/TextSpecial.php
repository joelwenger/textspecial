<?php

namespace joelwenger\textspecial\src;


class TextSpecial
{
    private $settings = [];
    private $data = [];
    private $index = '';
    private $colPointer = -1;
    private $colWidth = 0;
    private $countWords = 0;
    private $countElements = 0;
    private $showReadingTime = 0;
    private $showIndex = 0;


    /**
     * textSpecial constructor.
     *
     * @param string $aPath
     * @param integer $aSize
     */
    public function __construct()
    {
        // init settings
        $this->settings = [
            'debug' => FALSE,
            'pathimg' => 'img/',
            'font_family' => 'Verdana,Arial,Helvetica,sans-serif',
            'color' => '#697777',
            'background' => '#F2F5F5',
            'table_title_bg_color' => '#888888',
            'table_title_text_color' => '#ffffff',
            'table_td_bg_color' => '#cccccc',
            'table_td_text_color' => '#000000',
            'code_text_color' => '#2e6e3c',
            'code_bg_color' => '#cccccc',
            'marking_color' => '#ff0000',
        ];
    }


    /**
     * @param $aKey
     * @param $aValue
     *
     * @return false|void
     */
    public function setSetting( $aKey, $aValue )
    {
        // set a single setting
        $key = trim( strval( $aKey ) );
        if( $key == '' ) return FALSE;
        if( !isset( $this->settings[$key] ) ) return FALSE;
        $this->settings[$key] = $aValue;
    }


    /**
     * @param $aSettings
     */
    public function setSettings( $aSettings )
    {
        // set multiple settings at once
        if( !is_array( $aSettings ) ) return;
        foreach( $aSettings as $key => $val )
        {
            if( isset( $this->settings[$key] ) ) $this->settings[$key] = $val;
        }
    }


    /**
     * @param string $aPath
     */
    public function setFile( $aPath = "" )
    {
        // init
        $this->data = [];
        $this->index = '';
        $this->colPointer = -1;
        $this->colWidth = 0;
        $this->countWords = 0;
        $this->countElements = 0;

        // check
        $path = trim( $aPath );
        if( !is_file( $path ) ) return;

        // collect data
        $data = [];
        $handle = fopen( $path, 'r' );
        while( !feof( $handle ) )
        {
            $line = rtrim( fgets( $handle ) );
            $data[] = $this->convertRowToData( $line );
        }
        fclose( $handle );

        // parse data
        $this->prepareDataForParsing( $data );
    }


    /**
     * @param string $aPath
     */
    public function setText( $aText = "" )
    {
        // init
        $this->data = [];
        $this->index = '';
        $this->colPointer = -1;
        $this->colWidth = 0;
        $this->countWords = 0;
        $this->countElements = 0;

        // check
        $text = trim( $aText );
        if( trim( $text ) == "" ) return;

        // split into rows
        $text = str_replace( "\r\n", "\r", $text );
        $text = str_replace( "\n\r", "\r", $text );
        $text = str_replace( "\r", "\n", $text );
        $rows = explode( "\n", $text );

        // collect data
        $data = [];
        for( $i = 0; $i < count( $rows ); $i++ )
        {
            $line = rtrim( $rows[$i] );
            $data[] = $this->convertRowToData( $line );
        }

        // parse data
        $this->prepareDataForParsing( $data );
    }


    /**
     * @param int $aDebug
     *
     * @return string
     */
    public function getCSS( $aDebug = 0 )
    {
        // we do not use any css framework, we use our own grid
        $css = ".ts { font-family: ".$this->settings["font_family"].";   
                position: relative; display: grid; grid-template-columns: repeat(9, minmax(50px, 800px)); 
                width: 100%; }
            .ts .c1, .ts .c2, .ts .c3, .ts .c4, .ts .c5, .ts .c6, .ts .c7, .ts .c8, .ts .c9 {
                padding: 3px; counter-reset:ltbaenumeration; }
            .ts .c2 { grid-column: span 2; }
            .ts .c3 { grid-column: span 3; }
            .ts .c4 { grid-column: span 4; }
            .ts .c5 { grid-column: span 5; }
            .ts .c6 { grid-column: span 6; }
            .ts .c7 { grid-column: span 7; }
            .ts .c8 { grid-column: span 8; }
            .ts .c9 { grid-column: span 9; }
            .ts .mitte, .ts .mitte * { text-align:center; }\n"
            .".ts .rechts, .ts .rechts * { text-align:right; }\n";

        // to debug
        if( $this->settings['debug'] ) $css .= ".ts div { border:1px solid #000000; }\n";

        // anchors
        $css .= ".ts a, .ts a:visited { color:".$this->settings["color"]."; text-decoration:underline; }
            .ts a:hover { color:".$this->settings["color"]."; text-decoration:underline; }\n";

        // paragraphs
        $css .= ".ts p { padding:0; margin:0; text-align:justify; }
            .ts p.regular { margin:8px 8px 8px 8px; }\n";

        // titles
        $css .= ".ts h1, .ts h2, .ts h3, .ts h4  { padding:0; margin:0 0 10px 0; color:".$this->settings["color"]."; border-bottom:none; }
            .ts h1 { font-size:23px; }
            .ts h1.underlined { border-bottom:2px solid ".$this->settings["color"]."; }
            .ts h2 { font-size:21px; }
            .ts h2.underlined { border-bottom:2px solid ".$this->settings["color"]."; }
            .ts h3 { font-size:19px; }
            .ts h3.underlined { border-bottom:2px solid ".$this->settings["color"]."; }
            .ts h4 { font-size:17px; }
            .ts h4.underlined { border-bottom:2px solid ".$this->settings["color"]."; }\n";

        // jump marks
        $css .= ".ts .ajumpa { display: inline-block; width: 0; height: 0; border-left: 6px solid transparent;  
            border-right: 6px solid transparent; 
            border-bottom: 10px solid ".$this->settings["color"]."; 
            margin: 0 0 2px 5px; }\n";

        // initials
        $css .= ".ts p.capital { margin:24px 10px 10px 10px; }
            .ts p.capital  span.fc { display:block; height:54px; float:left; font-size:360%; vertical-align:top; 
                padding:0px 0px 0px 0px; margin:-19px 0px 0px -19px; border:0px solid #ff0000; 
                font-family: \"ZallmanCaps\",\"TexGyreSchola\",Verdana,Arial,Helvetica,sans-serif; }\n";

        // lists
        $css .= ".ts p.list { display:list-item; position:relative; list-style-type:square; } 
            .ts p.indended { display:list-item; position:relative; list-style-type:none; }
            .ts p.enumeration { position:relative; }
            .ts p.enumeration:before { content: counter(ltbaenumeration) '. '; counter-increment:ltbaenumeration; 
                margin-left:-1em; }
            .ts p.la { margin:0 0 0.5em 2em; }
            .ts p.lb { margin:0 0 0.5em 4em; }
            .ts p.lc { margin:0 0 0.5em 6em; }
            .ts p.ld { margin:0 0 0.5em 8em; }
            .ts p.le { margin:0 0 0.5em 10em; }
            .ts p.lf { margin:0 0 0.5em 12em; }
            .ts p.lg { margin:0 0 0.5em 14em; }
            .ts p.lh { margin:0 0 0.5em 16em; }
            .ts p.li { margin:0 0 0.5em 18em; }
            .ts p.lj { margin:0 0 0.5em 20em; }
            .ts p.lk { margin:0 0 0.5em 22em; }
            .ts p.ll { margin:0 0 0.5em 24em; }\n";

        // tables
        $css .= ".ts table.tablea { margin:10px; padding:0px; } 
            .ts table.tablea td { color:".$this->settings['table_td_text_color']."; 
                background:".$this->settings['table_td_bg_color']."; font-weight:normal; padding:2px 5px 2px 5px;  }
            .ts table.tablea td.center { text-align:center; }
            .ts table.tablea td.right { text-align:right; }
            .ts table.tablea tr.title td { color:".$this->settings['table_title_text_color']."; 
                background:".$this->settings['table_title_bg_color']."; font-weight:bold; padding:5px 5px; }\n";

        // quotes and code
        $css .= ".ts .tsquote { position:relative: display:block; width:calc( 100% - 20px ); overflow:auto; 
                margin:10px; padding:0; border:1px solid #000000; }
            .ts .tsquote pre { margin:0; padding:10px; font-style:italic; }
            .ts .tscode { position:relative: display:block; width:calc( 100% - 20px ); overflow:auto; margin:10px; 
                padding:0px; background:".$this->settings['code_bg_color']."; }
            .ts .tscode pre { margin:0; padding:10px; font-family:monospace; }
            .ts quote { color:".$this->settings['code_text_color']."; font-family:Courier; }\n";

        // video
        $css .= ".ts iframe.video { margin:10px; padding:0; border:0px; }\n";

        // readingtime
        $css .= ".ts div.readingtime { background:".$this->settings['code_bg_color']."; text-align:center; 
            padding:10px 7px; }\n";

        // colorcodes
        $css .= ".ts .red { color:#ff0000; }
            .ts .green { color:#00ff00; }
            .ts .orange { color:#ff8000; }
            .ts .gray { color:#aaaaaa; }\n";

        // marking
        $css .= ".ts .marked, .ts .marked * { color:".$this->settings["marking_color"]." !important; } 
            .ts p.marked img { border:1px solid ".$this->settings["marking_color"]." !important; }
            .ts iframe.marked { border:1px solid ".$this->settings["marking_color"]." !important; }
            .ts h1.underlined.marked { border-bottom:2px solid ".$this->settings["marking_color"]."; }
            .ts h2.underlined.marked { border-bottom:2px solid ".$this->settings["marking_color"]."; }
            .ts h3.underlined.marked { border-bottom:2px solid ".$this->settings["marking_color"]."; }
            .ts h4.underlined.marked { border-bottom:2px solid ".$this->settings["marking_color"]."; }
            .ts .marked .ajumpa { border-bottom: 10px solid ".$this->settings["marking_color"]." !important; }\n";


        // minify CSS
        $css = preg_replace( '/:\s+/', ':', $css );
        $css = preg_replace( '/\s+/', ' ', $css );
        return $css;
    }


    /**
     * @return string
     */
    public function getHtml()
    {
        // check
        if( count( $this->data ) == 0 ) return '';

        // generate grid rows
        $content = "";
        for( $i = 0; $i < count( $this->data ); $i++ )
        {
            $content .= $this->parseHtmlSwitch( $this->data[$i] );
        }
        if( trim( $content ) == '' ) return '';

        // complete last cells
        $content .= "</div>\n";
        $spalteNr = 9 - $this->colPointer;
        if( $spalteNr > 0 )
        {
            $this->colPointer = 0;
            $content .= "<div class='c".$spalteNr."'></div>\n";
        }

        // done
        return "<div class='ts'>\n".$content."</div>\n";
    }


    /**
     * @param $Row
     *
     * @return array
     */
    private function convertRowToData( $Row )
    {
        // closure to get metadata for rows (marking, comment, indentation)
        $getMetaData = function( $aLine )
        {
            // get indentation
            $line = rtrim( $aLine );
            $indentation = str_repeat( " ", strlen( $line ) - strlen( ltrim( $line ) ) );
            $line = trim( $line );

            // loop through array twice, because combinations are possible
            $f = [ "#" => 0, "+" => 0 ];
            for( $i = 0; $i < count( $f ); $i++ )
            {
                for( $j = 0; $j < count( $f ); $j++ )
                {
                    $c = substr( $line, 0, 1 );
                    if( isset( $f[$c] ) )
                    {
                        $f[$c] = 1;
                        $line = substr( $line, 1 );
                    }
                }
            }

            // return metadata
            return [
                "comment" => $f["#"],
                "marked" => $f["+"],
                "row" => $indentation.$line,
                "original" => $aLine,
            ];
        };

        // closure to join line data
        $getRowData = function( $aTyp, $aSub, $aMeta )
        {
            $this->countElements++;
            return [
                "id" => $this->countElements,
                "typ" => $aTyp,
                "sub" => $aSub,
                "comment" => $aMeta["comment"],
                "marked" => $aMeta["marked"],
                "row" => $aMeta["row"],
                "original" => $aMeta["original"],
            ];
        };

        // get metadata from row
        $meta = $getMetaData( $Row );
        if( trim( $meta["row"] ) == "" ) return $getRowData( "leer", "", $meta );
        if( trim( $meta["comment"] ) ) return $getRowData( "comment", "", $meta );

        // get key from row
        $pos = strpos( $meta["row"], ':' );
        if( !$pos ) return $getRowData( "unknown", "", $meta );
        $key = trim( substr( $meta["row"], 0, $pos ) );
        $meta["row"] = substr( $meta["row"], $pos + 1 );

        // get data according to key
        switch( $key )
        {
            // columns
            case 's': # old
                return $getRowData( "spalte", "", $meta );
            case 'c': # new
                return $getRowData( "column", "", $meta );

            // general
            case 'p': # paragraph
                return $getRowData( "paragraph", "", $meta );
            case 'cap': # paragraph with majuscule
                return $getRowData( 'majuscule', '', $meta );
            case 'i': # image
                return $getRowData( 'image', '', $meta );

            // titles
            case 'h1': # not underlined
                return $getRowData( 'title', '1', $meta );
            case 'h2': # not underlined
                return $getRowData( 'title', '2', $meta );
            case 'h3': # not underlined
                return $getRowData( 'title', '3', $meta );
            case 'h4': # not underlined
                return $getRowData( 'title', '4', $meta );
            case 'h1u': # underlined
                return $getRowData( 'title', '1u', $meta );
            case 'h2u': # underlined
                return $getRowData( 'title', '2u', $meta );
            case 'h3u': # underlined
                return $getRowData( 'title', '3u', $meta );
            case 'h4u': # underlined
                return $getRowData( 'title', '4u', $meta );

            // quotes
            case 'q': # quote single line
            case 'z': # quote single line
                return $getRowData( 'quote', 'single', $meta );
            case 'qs': # quote multiline start
                return $getRowData( 'quote', 'start', $meta );
            case 'qe': # quote multiline end
                return $getRowData( 'quote', 'end', $meta );

            // code
            case 'src': # code single line
                return $getRowData( 'code', 'single', $meta );
            case 'srcs': # code multiline start
                return $getRowData( 'code', 'start', $meta );
            case 'srce': # code multiline end
                return $getRowData( 'code', 'end', $meta );

            // lists
            case 'pl':
            case 'pl1':
                return $getRowData( 'list', 'la', $meta );
            case 'pl2':
                return $getRowData( 'list', 'lb', $meta );
            case 'pl3':
                return $getRowData( 'list', 'lc', $meta );
            case 'pl4':
                return $getRowData( 'list', 'ld', $meta );
            case 'pl5':
                return $getRowData( 'list', 'le', $meta );
            case 'pl6':
                return $getRowData( 'list', 'lf', $meta );
            case 'pl7':
                return $getRowData( 'list', 'lg', $meta );
            case 'pl8':
                return $getRowData( 'list', 'lh', $meta );
            case 'pl9':
                return $getRowData( 'list', 'li', $meta );
            case 'pl10':
                return $getRowData( 'list', 'lj', $meta );
            case 'pl11':
                return $getRowData( 'list', 'lk', $meta );
            case 'pl12':
                return $getRowData( 'list', 'll', $meta );

            // enumerations
            case 'pn':
            case 'pn1':
                return $getRowData( 'enumeration', 'la', $meta );
            case 'pn2':
                return $getRowData( 'enumeration', 'lb', $meta );
            case 'pn3':
                return $getRowData( 'enumeration', 'lc', $meta );
            case 'pn4':
                return $getRowData( 'enumeration', 'ld', $meta );
            case 'pn5':
                return $getRowData( 'enumeration', 'le', $meta );
            case 'pn6':
                return $getRowData( 'enumeration', 'lf', $meta );
            case 'pn7':
                return $getRowData( 'enumeration', 'lg', $meta );
            case 'pn8':
                return $getRowData( 'enumeration', 'lh', $meta );
            case 'pn9':
                return $getRowData( 'enumeration', 'li', $meta );
            case 'pn10':
                return $getRowData( 'enumeration', 'lj', $meta );
            case 'pn11':
                return $getRowData( 'enumeration', 'lk', $meta );
            case 'pn12':
                return $getRowData( 'enumeration', 'll', $meta );

            // indentations
            case 'pp':
            case 'pp1':
                return $getRowData( 'indended', 'la', $meta );
            case 'pp2':
                return $getRowData( 'indended', 'lb', $meta );
            case 'pp3':
                return $getRowData( 'indended', 'lc', $meta );
            case 'pp4':
                return $getRowData( 'indended', 'ld', $meta );
            case 'pp5':
                return $getRowData( 'indended', 'le', $meta );
            case 'pp6':
                return $getRowData( 'indended', 'lf', $meta );
            case 'pp7':
                return $getRowData( 'indended', 'lg', $meta );
            case 'pp8':
                return $getRowData( 'indended', 'lh', $meta );
            case 'pp9':
                return $getRowData( 'indended', 'li', $meta );
            case 'pp10':
                return $getRowData( 'indended', 'lj', $meta );
            case 'pp11':
                return $getRowData( 'indended', 'lk', $meta );
            case 'pp12':
                return $getRowData( 'indended', 'll', $meta );

            // tables
            case 'tables':
                return $getRowData( 'table', 'start', $meta );
            case 'tablee':
                return $getRowData( 'table', 'end', $meta );

            // html
            case 'html':
                return $getRowData( 'html', 'single', $meta );
            case 'htmls':
                return $getRowData( 'html', 'start', $meta );
            case 'htmle':
                return $getRowData( 'html', 'end', $meta );

            // other
            case 'readingtime':
                return $getRowData( 'readingtime', '', $meta );
            case 'index':
                return $getRowData( 'index', '', $meta );
            case 'clock':
                return $getRowData( 'clock', '', $meta );
            case 'star':
                return $getRowData( 'star', '', $meta );

            // video
            case 'vimeo':
                return $getRowData( 'vimeo', '', $meta );
            case 'youtube':
                return $getRowData( 'youtube', '', $meta );
        }

        // if arrived here, the line cannot be parsed
        $meta['row'] = $meta['original'];
        return $getRowData( "unknown", "", $meta );
    }


    /**
     *
     */
    private function prepareDataForParsing( $aData )
    {
        // init
        $this->data = [];
        $this->index = '';
        $this->colPointer = -1;
        $this->countWords = 0;
        $this->showReadingTime = 0;
        $this->showIndex = 0;

        // loop through data
        $multilineMode = 0;
        $multilineCache = '';
        foreach( $aData as $row )
        {
            // set flags
            if( $row["typ"] == "readingtime" ) $this->showReadingTime = 1;
            if( $this->showIndex == 1 && $row["typ"] == "index" ) continue; # skip doppelte index
            if( $row["typ"] == "index" ) $this->showIndex = 1;

            // we need h2's for index
            if( $row["typ"] == "title" && in_array( $row["sub"], [ '2', '2u' ] ) )
            {
                $this->index .= "<p class=\"list la\"><a href=\"#".$row["id"]."\" name=\"index".$row["id"]."\">".$row["row"]."</a></p>\n";
            }

            // cache, if multiline is switched on
            if( $multilineMode && $row['sub'] != 'end' )
            {
                $multilineCache .= $row['original']."\n";
                continue;
            }

            // multiline
            if( in_array( $row['typ'], [ 'quote', 'code', 'html', 'table' ] ) )
            {
                if( $row['sub'] == 'start' )
                {
                    $multilineMode = 1;
                    $multilineCache .= $row['row']."\n";
                    continue;
                }
                elseif( $row['sub'] == 'end' )
                {
                    $row['row'] = $multilineCache;
                    $row['sub'] = 'single';
                    $multilineMode = 0;
                    $multilineCache = '';
                }
            }

            // skip comment
            if( $row['typ'] == 'comment' ) continue;

            // collect data
            $this->data[] = $row;
            $this->countWords += str_word_count( $row["row"] );
        }
    }


    /**
     * @param array $aRow
     *
     * @return string
     */
    private function parseHtmlSwitch( array $aRow )
    {
        // convert to html
        switch( $aRow["typ"] )
        {
            case 'column':
                return $this->parseHtmlColumn( $aRow['row'] );
            case 'paragraph':
            case 'majuscule':
                return $this->parseHtmlContent( $aRow );
            case 'image':
                return $this->parseHtmlImg( $aRow );
            case 'title':
                return $this->parseHtmlTitel( $aRow );
            case 'quote':
            case 'code':
            case 'list':
            case 'enumeration':
            case 'indended':
            case 'table':
            case 'html':
            case 'index':
                return $this->parseHtmlContent( $aRow );
            case 'readingtime':
                return $this->parseHtmlReadingTime( $aRow );
            case 'clock':
                return $this->parseHtmlClock( $aRow );
            case 'star':
                return $this->parseHtmlStar( $aRow );
            case 'youtube':
            case 'vimeo':
                return $this->parseHtmlVideo( $aRow );
        }

        //  skip
        if( $aRow["comment"] ) return "";
        if( $aRow["typ"] == "leer" ) return "";
        if( trim( $aRow["row"] ) == "" ) return "";

        // when arrived here, unknown key, ignore
        return "<p><span class=\"red\">unknown:</span> ".$aRow["row"]."</p>";
    }


    /**
     * @param $aSpalte
     *
     * @return string
     */
    private function parseHtmlColumn( $aSpalte )
    {
        // split params
        $params = explode( "|", trim( $aSpalte ) );

        // new column
        if( !isset( $params[0] ) ) return "";
        $spalteNeu = intval( $params[0] );
        if( $spalteNeu < 1 || $spalteNeu > 9 ) return "";
        $this->colWidth = $spalteNeu;

        // alignment
        $ausrichtung = isset( $params[1] ) ? trim( strtolower( $params[1] ) ) : "";
        $class = '';
        if( in_array( $ausrichtung, [ 'r' ] ) ) $class = " rechts";
        if( in_array( $ausrichtung, [ 'm', 'c', 'z' ] ) ) $class = " mitte";

        // if necessary, close
        $html = "</div>\n";
        if( $this->colPointer == -1 )
        {
            $html = "";
            $this->colPointer = 0;
        }

        // otherwhise add column
        $spalteTotal = $this->colPointer + $this->colWidth;
        if( $spalteTotal <= 9 )
        {
            // less cells than grid row contains, therefore add new column to existing row
            $this->colPointer += $this->colWidth;
            $html .= "<div class='c".$this->colWidth.$class."'>";
            return $html;
        }
        elseif( $spalteTotal > 9 )
        {
            // contains more cells than grid row, therefore end old column first
            $spalteNr = 9 - $this->colPointer;
            if( $spalteNr > 0 ) $html .= "<div class='c".$spalteNr."'></div>\n";

            // start new column
            $this->colPointer = $this->colWidth;
            $html .= "<div class='c".$this->colWidth.$class."'>";
            return $html;
        }

        // when arrived here something went wrong
        return "";
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlTitel( $aRS )
    {
        // check
        if( trim( $aRS["row"] ) == "" ) return "";

        // get class
        $sub = $aRS["sub"];
        $underlined = 0;
        if( substr( $sub, -1 ) == "u" )
        {
            $sub = substr( $sub, 0, -1 );
            $underlined = 1;
        }
        $class = $aRS["marked"] ? "marked" : "";
        if( $underlined ) $class = trim( $class." underlined" );
        if( $class != "" ) $class = " class=\"".$class."\"";

        // get jump mark
        $jumpmark = "";
        if( $sub == "2" && $this->showIndex ) $jumpmark = "<a href=\"#index".$aRS["id"]."\" name=\"".$aRS["id"]."\" class=\"ajumpa\"></a>";

        // set title
        return "<h".$sub.$class.">".$aRS["row"].$jumpmark."</h".$sub.">";
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlContent( $aRS )
    {
        // switch to parse some keys into html
        $marked = $aRS["marked"] ? "marked" : "";
        switch( $aRS["typ"] )
        {
            case "paragraph":
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = $this->parseBasicBBCodes( $aRS["row"] );
                $class = trim( "regular ".$marked );
                return "<p class='".trim( $class )."'>".$content."</p>";
            case "list":
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = $this->parseBasicBBCodes( $aRS["row"] );
                $class = trim( "list ".$aRS["sub"]." ".$marked );
                return "<p class='".trim( $class )."'>".$content."</p>";
            case "enumeration":
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = $this->parseBasicBBCodes( $aRS["row"] );
                $class = trim( "enumeration ".$aRS["sub"]." ".$marked );
                return "<p class='".trim( $class )."'>".$content."</p>";
            case "indended":
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = $this->parseBasicBBCodes( $aRS["row"] );
                $class = trim( "indended ".$aRS["sub"]." ".$marked );
                return "<p class='".trim( $class )."'>".$content."</p>";
            case "majuscule":
                if( trim( $aRS["row"] ) == "" ) return "";
                $txt = trim( $aRS["row"] );
                $firstchar = mb_substr( $txt, 0, 1 );
                $rest = mb_substr( $txt, 1 );
                $content = "<span class=\"fc\">".$firstchar."</span>".$this->parseBasicBBCodes( $rest );
                $class = trim( "capital ".$marked );
                return "<p class='".trim( $class )."'>".$content."</p>";
            case 'quote':
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = htmlspecialchars( trim( $aRS["row"] ) );
                $class = $aRS["marked"] ? " class=\"marked\"" : "";
                return "<div class=\"tsquote\"><pre".$class.">\n".$content."\n</pre></div>\n";
            case 'code':
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = htmlspecialchars( trim( $aRS["row"] ) );
                $class = $aRS["marked"] ? " class=\"marked\"" : "";
                return "<div class=\"tscode\"><pre".$class.">\n".$content."\n</pre></div>\n";
            case 'html':
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = trim( $aRS["row"] );
                $class = $aRS["marked"] ? " class=\"regular marked\"" : " class=\"regular\"";
                return "<p class=\"".$class."\">\n".$content."\n</p>";
            case 'table':
                if( trim( $aRS["row"] ) == "" ) return "";
                $content = $this->parseHtmlTable( trim( $aRS["row"] ) );
                $class = $aRS["marked"] ? " class=\"regular marked\"" : " class=\"regular\"";
                return "<p class=\"".$class."\">\n".$content."\n</p>";
            case 'index':
                $content = trim( $this->index );
                $class = $aRS["marked"] ? " class=\"regular marked\"" : " class=\"regular\"";
                return "<p class=\"".$class."\">\n".$content."\n</p>";
            default:
                return "";
        }
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlImg( $aRS )
    {
        // check
        if( trim( $aRS["row"] ) == "" ) return "";

        // split params
        $param = explode( "|", $aRS["row"] );
        if( !isset( $param[0] ) ) return "";

        // closure to get image tag
        $getHtmlImg = function( $aSrc )
        {
            // check file first
            $path = trim( $aSrc );
            if( !is_file( $path ) ) return "";

            // check extension
            $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
            if( $extension != "svg" )
            {
                // check image type
                $mImg = getimagesize( $path );
                $mP["typ"] = $mImg[2];
                if( !in_array( $mP["typ"], [ 1, 2, 3 ] ) ) return '';
            }

            // return image tag
            return "<img alt='' src='".$path."' style='max-width:100%;height:auto;'>";
        };

        // get title, if any
        $title = "";
        if( isset( $param[1] ) && trim( $param[1] ) != "" ) $title = "<br>".trim( $param[1] );

        // get image
        $pfad = $this->settings["pathimg"].trim( $param[0] );
        $img = trim( $param[0] );
        if( is_file( $pfad ) ) $img = $getHtmlImg( $pfad );

        // done
        $class = $aRS["marked"] ? " class=\"marked\"" : "";
        return "<p".$class." style=\"text-align:center;\">".$img.$title."</p>";
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlClock( $aRS )
    {
        // check
        if( $aRS["comment"] ) return "";
        $svg = [];

        // canvas
        $canvas = [];
        $canvas["x"] = 140;
        $canvas["y"] = 120;
        $canvas["xcenter"] = round( $canvas["x"] / 2 );
        $canvas["ycenter"] = round( $canvas["y"] / 2 );

        // split time, if any
        $h = rand( 0, 23 );
        $m = rand( 0, 59 );
        $s = rand( 0, 59 );
        $timeSelected = trim( $aRS["row"] );
        $regsearch = "'^([01][0-9]|2[0-3])\:([0-5][0-9])\:?([0-5][0-9])?h?'i";
        $time=[];
        if( $timeSelected == "" || !preg_match( $regsearch, $timeSelected, $myArray ) )
        {
            $time["h"]["val"] = $h;
            $time["m"]["val"] = $m;
            $time["s"]["val"] = $s;
        }
        else
        {
            $time["h"]["val"] = isset( $myArray[1] ) ? intval( $myArray[1] ) : $h;
            $time["m"]["val"] = isset( $myArray[2] ) ? intval( $myArray[2] ) : $m;
            $time["s"]["val"] = isset( $myArray[3] ) ? intval( $myArray[3] ) : $s;
        }

        // set angles
        $secondsTotal = 24 * 60 * 60;
        $secondsCalculated = $time["h"]["val"] * 60 * 60;
        $time["h"]["angle"] = 360 * $secondsCalculated / $secondsTotal;
        $secondsTotal = 60 * 60;
        $secondsCalculated = $time["m"]["val"] * 60;
        $time["m"]["angle"] = 360 * $secondsCalculated / $secondsTotal;
        $secondsTotal = 60;
        $secondsCalculated = $time["s"]["val"];
        $time["s"]["angle"] = 360 * $secondsCalculated / $secondsTotal;

        // set colors
        $colors = [
            "circle" => $this->settings["color"],
            "hourstrokes" => $this->settings["color"],
            "minutestrokes" => $this->settings["color"],
            "hourhand" => $this->settings["color"],
            "minutehand" => $this->settings["color"],
            "secondhand" => $this->settings["color"],
        ];

        // set circle
        $circle = [];
        $circle["xcenter"] = $canvas["xcenter"];
        $circle["ycenter"] = $canvas["ycenter"];
        $circle["radius"] = 0;
        if( $canvas["x"] > $canvas["y"] )
        {
            $circle["radius"] = ( $canvas["y"] / 2 ) - 5;
        }
        else $circle["radius"] = ( $canvas["x"] / 2 ) - 5;

        // SVG for circle
        $svgstyle = "opacity:1;fill:none;stroke:".$colors["circle"].";stroke-width:3;";
        $svgCircle = "<circle cx=\"".$circle["xcenter"]."\" cy=\"".$circle["ycenter"]."\" r=\"".$circle["radius"]."\"  style=\"".$svgstyle."\" />";
        $count = count( $svg );
        $svg[$count] = $svgCircle;

        // SVG for minutestrokes
        $anglesteps = 360 / 60;
        $width = round( $circle["radius"] / 32, 2 );
        $height = round( $circle["radius"] / 18, 2 );
        $x = round( $canvas["xcenter"] - $width / 2, 2 );
        $y = round( $canvas["ycenter"] - $circle["radius"] + 4, 2 );
        $svgstyle = "opacity:1;fill:".$colors["minutestrokes"].";stroke:none;";
        $svgrect = "<rect width=\"".$width."\" height=\"".$height."\" x=\"".$x."\" y=\"".$y."\" style=\"".$svgstyle."\"";
        for( $i = 0; $i < 60; $i++ )
        {
            $rotategrad = $i * $anglesteps;
            $transform = " transform=\"rotate(".$rotategrad.",".$canvas["xcenter"].",".$canvas["ycenter"].")\"";
            $count = count( $svg );
            $svg[$count] = $svgrect.$transform." />";
        }

        // SVG for hourstrokes
        $anglesteps = 360 / 12;
        $width = round( $circle["radius"] / 16, 2 );
        $height = round( $circle["radius"] / 6, 2 );
        $x = round( $canvas["xcenter"] - $width / 2, 2 );
        $y = round( $canvas["ycenter"] - $circle["radius"] + 4, 2 );
        $svgstyle = "opacity:1;fill:".$colors["hourstrokes"].";stroke:none;";
        $svgrect = "<rect width=\"".$width."\" height=\"".$height."\" x=\"".$x."\" y=\"".$y."\" style=\"".$svgstyle."\"";
        for( $i = 0; $i < 12; $i++ )
        {
            $rotategrad = $i * $anglesteps;
            $transform = " transform=\"rotate(".$rotategrad.",".$canvas["xcenter"].",".$canvas["ycenter"].")\"";
            $count = count( $svg );
            $svg[$count] = $svgrect.$transform." />";
        }

        // hourhand
        $width = round( $circle["radius"] / 11, 2 );
        $height = round( $circle["radius"] * .7, 2 );
        $protruding = 6;
        $x = round( $canvas["xcenter"] - $width / 2, 2 );
        $y = round( $canvas["ycenter"] - $height + $protruding );
        $transform = " transform=\"rotate(".$time["h"]["angle"].",".$canvas["xcenter"].",".$canvas["ycenter"].")\"";
        $svgstyle = "opacity:1;fill:".$colors["hourhand"].";stroke:none;";
        $svgrect = "<rect width=\"".$width."\" height=\"".$height."\" x=\"".$x."\" y=\"".$y."\" style=\"".$svgstyle."\"".$transform." />";
        $count = count( $svg );
        $svg[$count] = $svgrect.$transform." />";

        // minutehand
        $width = round( $circle["radius"] / 11, 2 );
        $height = $circle["radius"] - 4;
        $protruding = 6;
        $x = round( $canvas["xcenter"] - $width / 2, 2 );
        $y = round( $canvas["ycenter"] - $height + $protruding );
        $transform = " transform=\"rotate(".$time["m"]["angle"].",".$canvas["xcenter"].",".$canvas["ycenter"].")\"";
        $svgstyle = "opacity:1;fill:".$colors["minutehand"].";stroke:none;";
        $svgrect = "<rect width=\"".$width."\" height=\"".$height."\" x=\"".$x."\" y=\"".$y."\" style=\"".$svgstyle."\"".$transform." />";
        $count = count( $svg );
        $svg[$count] = $svgrect.$transform." />";

        // secondhand
        $width = round( $circle["radius"] / 22, 2 );
        $height = $circle["radius"];
        $protruding = 6;
        $x = round( $canvas["xcenter"] - $width / 2, 2 );
        $y = round( $canvas["ycenter"] - $height + $protruding );
        $transform = " transform=\"rotate(".$time["s"]["angle"].",".$canvas["xcenter"].",".$canvas["ycenter"].")\"";
        $svgstyle = "opacity:1;fill:".$colors["secondhand"].";stroke:none;";
        $svgrect = "<rect width=\"".$width."\" height=\"".$height."\" x=\"".$x."\" y=\"".$y."\" style=\"".$svgstyle."\"".$transform." />";
        $count = count( $svg );
        $svg[$count] = $svgrect.$transform." />";

        // put SVG together
        $svgContent = "<?xml version=\"1.0\" standalone=\"no\"?>\n"
            ."<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n"
            ."<svg width=\"".$canvas["x"]."px\" height=\"".$canvas["y"]."px\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\">\n"
            .implode( "\n", $svg )."\n"
            ."</svg>\n";

        // output as base64 image
        $base64 = base64_encode( $svgContent );
        $img = "<img alt=\"\" src=\"data:image/svg+xml;base64,".$base64."\" width=\"".$canvas["x"]."\" height=\"".$canvas["y"]."\">";
        $class = $aRS["marked"] ? " class=\"marked\"" : "";
        $html = "<p".$class." style=\"text-align:center;\">".$img."</p>";

        // done
        return $html;
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlStar( $aRS, $aDebug = 0 )
    {
        // width and height
        $mX = 140;
        $mY = 120;

        // set circle
        $circle = [];
        $circle["xcenter"] = round( $mX / 2 );
        $circle["ycenter"] = round( $mY / 2 );
        $circle["radius"] = 0;
        if( $mX > $mY )
        {
            $circle["radius"] = ( $mY / 2 ) - 5;
        }
        else $circle["radius"] = ( $mX / 2 ) - 5;

        // random points in circle
        $i = 0;
        $k = [];
        while( $i < 12 )
        {
            // random coodinates
            $randx = rand( 1, $circle["radius"] );
            $randy = rand( 1, $circle["radius"] );

            // get sides of triangle
            $hx = $circle["radius"] + $randx;
            $c = $circle["radius"] * 2;
            $a = sqrt( pow( $c - $hx, 2 ) + pow( $randy, 2 ) );
            $b = sqrt( pow( $hx, 2 ) + pow( $randy, 2 ) );

            // check if not in circle
            if( pow( $a, 2 ) + pow( $b, 2 ) < pow( $c, 2 ) )
            {
                // multiplikator for four circle segments
                $multx = rand( 0, 1 ) == 1 ? -1 : 1;
                $multy = rand( 0, 1 ) == 1 ? -1 : 1;

                // store coordinates in array
                $i = count( $k );
                $k[$i]["x"] = $circle["xcenter"] + $randx * $multx;
                $k[$i]["y"] = $circle["ycenter"] + $randy * $multy;
            }
        }

        // set SVG pathes
        $svgcontent = "";
        if( !$aDebug )
        {
            // set a path
            $i = 0;
            $path[$i]["color"] = $this->settings["color"];
            $path[$i]["shape"] = "M ".$k[0]["x"]." ".$k[0]["y"]." "
                ."L ".$k[1]["x"]." ".$k[1]["y"]." "
                ."L ".$k[2]["x"]." ".$k[2]["y"]." "
                ."L ".$k[3]["x"]." ".$k[3]["y"]." "
                ."L ".$k[4]["x"]." ".$k[4]["y"]." "
                ."L ".$k[5]["x"]." ".$k[5]["y"]." "
                ."L ".$k[6]["x"]." ".$k[6]["y"]." "
                ."L ".$k[7]["x"]." ".$k[7]["y"]." "
                ."L ".$k[0]["x"]." ".$k[0]["y"]." z";

            // put together
            $svgcontent = "";
            for( $i = 0; $i < count( $path ); $i++ )
            {
                $rotatecycle = rand( 4, 6 );
                $rotatesteps = round( 360 / $rotatecycle, 3 );
                $rotateopacity = round( 1 / $rotatecycle, 3 );
                $svgstyle = " style=\"opacity:1;fill:".$path[$i]["color"].";fill-opacity:".$rotateopacity.";stroke:none;\"";
                for( $mj = 0; $mj < $rotatecycle; $mj++ )
                {
                    $rotategrad = $mj * $rotatesteps;
                    $transform = " transform=\"rotate(".$rotategrad.",".$circle["xcenter"].",".$circle["ycenter"].")\"";
                    $svgcontent .= "<path d=\"".$path[$i]["shape"]." \"".$svgstyle.$transform." />\n";
                }
            }
        }

        // debug
        if( $aDebug )
        {

            // show circles
            $svgstyle = " style=\"opacity:1;fill:none;stroke:#000000;stroke-width:1;\"";
            $svgcontent = "<circle cx=\"".$circle["xcenter"]."\" cy=\"".$circle["ycenter"]."\" r=\"".$circle["radius"]."\" ".$svgstyle." />\n";

            // show points
            for( $i = 0; $i < 9; $i++ )
            {
                $svgstyle = " style=\"opacity:1;fill:#000000;\"";
                $svgcontent .= "<circle cx=\"".$k[$i]["x"]."\" cy=\"".$k[$i]["y"]."\" r=\"1\" ".$svgstyle." />\n";
            }
        }

        // put SVG together
        $svg = "<?xml version=\"1.0\" standalone=\"no\"?>\n"
            ."<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n"
            ."<svg width=\"".$mX."px\" height=\"".$mY."px\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\">\n"
            .$svgcontent
            ."</svg>\n";

        // output as base64 image
        $base64 = base64_encode( $svg );
        $class = $aRS["marked"] ? " class=\"marked\"" : "";
        $img = "<img alt=\"\" src=\"data:image/svg+xml;base64,".$base64."\" width=\"".$mX."\" "
            ."height=\"".$mY."\">";
        $html = "<p".$class." style=\"text-align:center;\">".$img."</p>";

        // done
        return $html;
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlVideo( $aRS )
    {
        // check
        if( trim( $aRS['row'] ) == '' ) return '';

        // get type
        $videotyp = trim( $aRS["typ"] );
        if( !in_array( $videotyp, [ 'youtube', 'vimeo' ] ) ) return '';

        // some settings
        $width = 547;
        $height = 334;
        $videoid = trim( $aRS["row"] );

        // html for Youtoube or Vimeo
        $class = $aRS["marked"] ? " marked" : "";
        if( $videotyp == "youtube" )
        {
            return "<iframe width=\"".$width."\" height=\"".$height."\" "
                ."src=\"//www.youtube.com/embed/".$videoid."?rel=0&showinfo=0\" "
                ."class=\"video".$class."\" allowfullscreen></iframe>";
        }
        elseif( $videotyp == "vimeo" )
        {
            return "<iframe src=\"//player.vimeo.com/video/".$videoid."\" "
                ."width=\"".$width."\" height=\"".$height."\" "
                ."class=\"video".$class."\" allowfullscreen></iframe>";
        }

        // if arrived here, no known video
        return '';
    }


    /**
     * @param $aRS
     *
     * @return string
     */
    private function parseHtmlReadingTime( $aRS )
    {
        // check
        if( !$this->showReadingTime ) return '';
        if( $this->countWords < 1 ) return '';

        // get readingtime
        $wordcountshow = number_format( $this->countWords, 0, ",", "'" );
        $minutes = round( $this->countWords / 220 );
        $hours = floor( $minutes / 60 );
        $minutes -= $hours * 60;
        $showminutes = substr( "00".$minutes, -2 );

        // show readingtime
        return "<div class='readingtime'>this article has ".$wordcountshow." words, "
            ."estimated reading time ".$hours.":".$showminutes."h</div>\n";
    }


    /**
     * @param $aContent
     *
     * @return string
     */
    private function parseHtmlTable( $aContent )
    {
        // split into rows
        $content = trim( $aContent );
        $content = str_replace( "\r\n", "\r", $content );
        $content = str_replace( "\n\r", "\r", $content );
        $content = str_replace( "\r", "\n", $content );
        $rows = explode( "\n", $content );
        if( count( $rows ) < 3 ) return '';

        // closure to trim single rows
        $trimRow = function( $aRow )
        {
            $row = trim( $aRow );
            $row = ltrim( $row, '|' );
            $row = rtrim( $row, '|' );
            return trim( $row );
        };

        // first get settings
        $settings = [];
        if( preg_match_all( '/(:?)(-+)(:?)\|?/', $trimRow( $rows[1] ), $matches ) )
        {
            $sizeCount = strlen( implode( $matches[2] ) );
            for( $j = 0; $j < count( $matches[0] ); $j++ )
            {
                $left = isset( $matches[1][$j] ) && $matches[1][$j] == ':' ? 1 : 0;
                $right = isset( $matches[3][$j] ) && $matches[3][$j] == ':' ? 1 : 0;
                $center = 0;
                if( $left && $right )
                {
                    $right = 0;
                    $center = 1;
                }
                $settings[$j] = [
                    'right' => $right,
                    'center' => $center,
                    'size' => isset( $matches[2][$j] ) ? strlen( $matches[2][$j] ) / $sizeCount * 100 : NULL,
                ];
            }
        }
        $colCount = count( $settings );
        $emptyArray = array_fill( 0, $colCount, '' );

        // closure to get table row
        $getTR = function( $aRow, $aTRClass = '' ) use ( $settings, $colCount, $emptyArray, $trimRow )
        {
            // init
            $split = explode( '|', $trimRow( $aRow ) );
            $split = array_merge( $split, $emptyArray );
            $split = array_slice( $split, 0, $colCount );

            // classes
            $trClass = trim( $aTRClass );
            if( $trClass != '' ) $trClass = ' class="'.$trClass.'"';

            // get table cells
            $td = [];
            for( $i = 0; $i < count( $split ); $i++ )
            {
                $tdClass = '';
                if( isset( $settings[$i]['center'] ) && $settings[$i]['center'] )
                {
                    $tdClass = ' class="center"';
                }
                elseif( isset( $settings[$i]['right'] ) && $settings[$i]['right'] )
                {
                    $tdClass = ' class="right"';
                }
                $td[] = "<td".$tdClass.">".trim( $split[$i] )."</td>\n";
            }

            // done
            return "<tr".$trClass.">\n".implode( "", $td )."\n</tr>\n";
        };

        // get colgroup
        $temp = [];
        for( $i = 0; $i < count( $settings ); $i++ )
        {
            $width = round( $settings[$i]['size'], 0 );
            $temp[] = '<col style="width:'.$width.'%">';
        }
        $colgroup = "<colgroup>\n".implode( "\n", $temp )."\n</colgroup>\n";

        // get title
        $title = $getTR( $rows[0], 'title' );

        // get table
        $temp = [];
        for( $i = 2; $i < count( $rows ); $i++ )
        {
            $temp[] = $getTR( $rows[$i] );
        }
        $table = "<table class='tablea'>\n"
            .$colgroup
            .$title
            .implode( "", $temp )
            ."</table>\n";

        // done
        return $table;
    }


    /**
     * @param $aContent
     *
     * @return null|string|string[]
     */
    private function parseBasicBBCodes( $aContent )
    {
        // init
        $content = stripslashes( $aContent );
        $regS = [];
        $regR = [];

        // text formatting
        //''''...''''
        $regS[] = "'\'\'\'\'(.*?)\'\'\'\''si";
        $regR[] = "<b><i>$1</i></b>";
        //'''...'''
        $regS[] = "'\'\'\'(.*?)\'\'\''si";
        $regR[] = "<i>$1</i>";
        //''...''
        $regS[] = "'\'\'(.*?)\'\''si";
        $regR[] = "<b>$1</b>";

        // text alignment
        // [center]...[/center]\n
        $regS[] = "'\[\s*center\s*\](.*?)\[\s*\/\s*center\s*\]'si";
        $regR[] = "<div style=\"text-align:center;\">$1</div>";
        // [right]...[/right]\n
        $regS[] = "'\[\s*right\s*\](.*?)\[\s*\/\s*right\s*\]'si";
        $regR[] = "<div style=\"text-align:right;\">$1</div>";

        // other
        // [red]
        $regS[] = "'\[\s*red\s*\](.*?)\[\s*\/\s*red\s*\]'si";
        $regR[] = "<span class=\"red\">$1</span>";
        // <red>
        $regS[] = "'<\s*red\s*>(.*?)<\s*\/\s*red\s*>'si";
        $regR[] = "<span class=\"red\">$1</span>";
        // [orange]
        $regS[] = "'\[\s*orange\s*\](.*?)\[\s*\/\s*orange\s*\]'si";
        $regR[] = "<span class=\"orange\">$1</span>";
        // <orange>
        $regS[] = "'<\s*orange\s*>(.*?)<\s*\/\s*orange\s*>'si";
        $regR[] = "<span class=\"orange\">$1</span>";
        // [green]
        $regS[] = "'\[\s*green\s*\](.*?)\[\s*\/\s*green\s*\]'si";
        $regR[] = "<span class=\"green\">$1</span>";
        // <green>
        $regS[] = "'<\s*green\s*>(.*?)<\s*\/\s*green\s*>'si";
        $regR[] = "<span class=\"green\">$1</span>";
        // [gray]
        $regS[] = "'\[\s*gray\s*\](.*?)\[\s*\/\s*gray\s*\]'si";
        $regR[] = "<span class=\"gray\">$1</span>";
        // <gray>
        $regS[] = "'<\s*gray\s*>(.*?)<\s*\/\s*gray\s*>'si";
        $regR[] = "<span class=\"gray\">$1</span>";

        // execute and return
        return preg_replace( $regS, $regR, $content );
    }
}
