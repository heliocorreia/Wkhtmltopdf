<?php
/**
 * @author aur1mas <aur1mas@devnet.lt>
 * @copyright aur1mas <aur1mas@devnet.lt>
 * @license http://framework.zend.com/license/new-bsd     New BSD License
 * @version 1.00
 */
class Wkhtmltopdf
{

    /**
     * setters / getters properties
     */
    protected $_html = null;
    protected $_orientation = null;
    protected $_pageSize = null;
    protected $_toc = false;
    protected $_copies = 1;
    protected $_grayscale = false;
    protected $_title = null;
    protected $_path;               // path to directory where to place files

    /**
     * path to executable
     */
    protected $_bin = '/usr/bin/wkhtmltopdf';
    protected $_filename = null;                // filename in $path directory

    /**
     * available page orientations
     */
    const ORIENTATION_PORTRAIT = 'Portrait';    // vertical
    const ORIENTATION_LANDSCAPE = 'Landscape';  // horizontal

    /**
     * page sizes
     */
    const SIZE_A4 = 'A4';
    const SIZE_LETTER = 'letter';

    /**
     * file get modes
     */
    const MODE_DOWNLOAD = 0;
    const MODE_STRING = 1;
    const MODE_EMBEDDED = 2;
    const MODE_SAVE = 3;

    /**
     * @author aur1mas <aur1mas@devnet.lt>
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (array_key_exists('html', $options)) {
            $this->setHtml($options['html']);
        }

        if (array_key_exists('orientation', $options)) {
            $this->setOrientation($options['orientation']);
        } else {
            $this->setOrientation(self::ORIENTATION_PORTRAIT);
        }

        if (array_key_exists('page_size', $options)) {
            $this->setPageSize($options['page_size']);
        } else {
            $this->setPageSize(self::SIZE_A4);
        }

        if (array_key_exists('toc', $options)) {
            $this->setTOC($options['toc']);
        }

        if (array_key_exists('binpath', $options)) {
            $this->setBinPath($options['binpath']);
        }

        if (array_key_exists('grayscale', $options)) {
            $this->setGrayscale($options['grayscale']);
        }

        if (array_key_exists('title', $options)) {
            $this->setTitle($options['title']);
        }

        if (!array_key_exists('path', $options)) {
            throw new Exception("Path to directory where to store files is not set");
        }

        $this->setPath($options['path']);

        $this->_createFile();
    }

    /**
     * creates file to which will be writen html content
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    protected function _createFile()
    {
        do {
            $this->_filename = $this->getPath() .  mt_rand() . '.html';
        } while(file_exists($this->_filename));

        /**
         * create an empty file
         */
        file_put_contents($this->_filename, $this->getHtml());

        return $this->_filename;
    }

    /**
     * returns file path where html content is saved
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filename;
    }

    /**
     * executes command
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @param string $cmd   command to execute
     * @param string $input other input (not arguments)
     * @return array
     */
    protected function _exec($cmd, $input = "")
    {
        $result = array('stdout' => '', 'stderr' => '', 'return' => '');

        $proc = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $result['stdout'] = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $result['stderr'] = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $result['return'] = proc_close($proc);

        return $result;
    }

    /**
     * returns help info
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    public function getHelp()
    {
        $r = $this->_exec($this->_bin . " --extended-help");
        return $r['stdout'];
    }

    /**
     * set HTML content to render
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @param string $html
     * @return Wkhtmltopdf
     */
    public function setHtml($html)
    {
        $this->_html = (string)$html;
        return $this;
    }

    /**
     * returns HTML content
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     * Absolute path where to store files
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @throws Exception
     * @param string $path
     * @return Wkhtmltopdf
     */
    public function setPath($path)
    {
        if (realpath($path) === false)
            throw new Exception("Path must be absolute");

        $this->_path = realpath($path) . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * returns path where to store saved files
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * set page orientation
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @param string $orientation
     * @return Wkhtmltopdf
     */
    public function setOrientation($orientation)
    {
        $this->_orientation = (string)$orientation;
        return $this;
    }

    /**
     * returns page orientation
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    public function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     * @author aur1mas <aur1mas@devnet.lt>
     * @param string $size
     * @return Wkhtmltopdf
     */
    public function setPageSize($size)
    {
        $this->_pageSize = (string)$size;
        return $this;
    }

    /**
     * returns page size
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * enable / disable generation Table Of Contents
     * @author aur1mas <aur1mas@devnet.lt>
     * @param boolean $toc
     * @return Core_Wkhtmltopdf
     */
    public function setTOC($toc = true)
    {
        $this->_toc = (boolean)$toc;
        return $this;
    }

    /**
     * returns value is enabled Table Of Contents generation or not
     *
     * @author aur1nas <aur1mas@devnet.lt>
     * @return boolean
     */
    public function getTOC()
    {
        return $this->_toc;
    }
    
    /**
     * returns bin path
     * 
     * @author heliocorreia <dev@heliocorreia.org>
     * @return string
     */
    public function getBinPath()
    {
    	return $this->_bin;
    }
    
    /**
     * returns bin path
     * 
     * @author heliocorreia <dev@heliocorreia.org>
     * @return string
     */
    public function setBinPath($path)
    {
    	if (file_exists($path))
    	{
    		$this->_bin = (string)$path;
    	}
   	
    	return Wkhtmltopdf;
    }

    /**
     * set number of copies
     * @author aur1mas <aur1mas@devnet.lt>
     * @param int $copies
     * @return Wkhtmltopdf
     */
    public function setCopies($copies)
    {
        $this->_copies = (int)$copies;
        return $this;
    }

    /**
     * returns  number of copies to make
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return int
     */
    public function getCopies()
    {
        return $this->_copies;
    }

    /**
     * whether to print in grayscale or not
     * @author aur1mas <aur1mas@devnet.lt>
     * @param boolean $mode
     * @return Wkhtmltopdf
     */
    public function setGrayscale($mode)
    {
        $this->_grayscale = (boolean)$mode;
        return $this;
    }

    /**
     * returns is page will be printed in grayscale format
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return boolean
     */
    public function getGrayscale()
    {
        return $this->_grayscale;
    }

    /**
     * PDF title
     * @author aur1mas <aur1mas@devnet.lt>
     * @param string $title
     * @return Wkhtmltopdf
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;
        return $this;
    }

    /**
     * returns PDF document title
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @throws Exception
     * @return string
     */
    public function getTitle()
    {
        if ($this->_title) {
            return $this->_title;
        }

        throw new Exception("Title is not set");
    }

    /**
     * returns command to execute
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @return string
     */
    protected function _getCommand()
    {
        $command = $this->_bin;

        $command .= ($this->getCopies() > 1) ? " --copies " . $this->getCopies() : "";
        $command .= " --orientation " . $this->getOrientation();
        $command .= " --page-size " . $this->getPageSize();
        $command .= ($this->getTOC()) ? " --toc" : "";
        $command .= ($this->getGrayscale()) ? " --grayscale" : "";
        $command .= ' --title "' . $this->getTitle() . '"';
        $command .= ' "' . $this->getFilePath() . '"';
        $command .= " -";

        return $command;
    }

    /**
     * @todo use file cache
     *
     * @author aur1mas <aur1mas@devnet.lt>
     * @throws Exception
     * @return string
     */
    protected function _render()
    {
        if (mb_strlen($this->_html, 'utf-8') === 0)
            throw new Exception("HTML content not set");

        file_put_contents($this->getFilePath(), $this->getHtml());
        $content = $this->_exec($this->_getCommand());

        if (strpos(mb_strtolower($content['stderr']), 'error'))
                throw new Exception("System error <pre>" . $content['stderr'] . "</pre>");

        if (mb_strlen($content['stdout'], 'utf-8') === 0)
               throw new Exception("WKHTMLTOPDF didn't return any data");

        if ((int)$content['return'] > 1)
            throw new Exception("Shell error, return code: " . (int)$content['return']);

        return $content['stdout'];
    }

    /**
     * @author aur1mas <aur1mas@devnet.lt>
     * @param int $mode
     * @param string $filename
     */
    public function output($mode, $filename)
    {
        switch ($mode) {
            case self::MODE_DOWNLOAD:
                if (!headers_sent()) {
                    header("Content-Description: File Transfer");
                    header("Cache-Control: public; must-revalidate, max-age=0");
                    header("Pragme: public");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                    header("Last-Modified: " . gmdate('D, d m Y H:i:s') . " GMT");
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octec-stream", false);
                    header("Content-Type: application/download", false);
                    header("Content-Type: application/pdf", false);
                    header('Content-Disposition: attachment; filename="' . basename($filename) .'";');
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length " . mb_strlen($this->_render()));
                    echo $this->_render();
                    unlink($this->getFilePath());
                    exit();
                } else {
                    throw new Exception("Headers already sent");
                }
                break;
            case self::MODE_STRING:
                return $this->_render();
                break;
            case self::MODE_EMBEDDED:
                if (!headers_sent()) {
                    header("Content-type: application/pdf");
                    header("Cache-control: public, must-revalidate, max-age=0");
                    header("Pragme: public");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                    header("Last-Modified: " . gmdate('D, d m Y H:i:s') . " GMT");
                    header("Content-Length " . mb_strlen($this->_render()));
                    header('Content-Disposition: inline; filename="' . basename($filename) .'";');
                    echo $this->_render();
                    unlink($this->getFilePath());
                    exit();
                } else {
                    throw new Exception("Headers already sent");
                }
                break;
            case self::MODE_SAVE:
                file_put_contents($filename, $this->_render());
                unlink($this->getFilePath());
                break;
            default:
                throw new Exception("Mode: " . $mode . " is not supported");
        }
    }
}