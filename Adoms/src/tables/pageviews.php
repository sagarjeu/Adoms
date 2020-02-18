<?php declare (strict_types = 1);
namespace Adoms\src\tables;

//namespace Adoms\src\lib;

$my = function ($pClassName) {
    include_once(dirname($_SERVER['DOCUMENT_ROOT']) . strtolower($pClassName) . ".php");
};
spl_autoload_register($my, true, 1);

class PageViews
{

    public $path;
    public $partials = array();
    public $token;
    public $injections = array();
    public $selector;

    /**
     * public function __construct
     * @parameters string, string, string
     */
    function __construct(string $token, string $view_name)
    {
        $this->token = $token;
        $this->path = "$this->token/view";

        if (!is_dir("$this->path/$view_name") && !mkdir("$this->path/$view_name"))
            echo "Unable to create needed directories";
        $this->copy = $view_name;
        $this->injections = [];
    }

    /**
     * public function addPartial
     * @parameters string, string, string
     * @return bool
     */
    public function addPartial(string $filename, string $view_name = "FALSE", string $res_dir = "FALSE")
    {
        if ($view_name == "FALSE")
            $view_name = $this->copy;
        if ($res_dir == "FALSE")
            $res_dir = "partials";
        $bool = 0;

        $exp_dir = explode('/', $res_dir);
        $kmd = "";
        foreach ($exp_dir as $k) {
            $kmd .= $k . '/';
            if (!is_dir("$this->path/$view_name/$kmd") && !mkdir("$this->path/$view_name/$kmd"))
                echo "Unable to create directories";
        }
        if (!is_dir("$this->path/$view_name/$res_dir") && !mkdir("$this->path/$view_name/$res_dir")) {
            echo "Unable to create directories needed";
            return false;
        }
        if (!file_exists("$this->path/$view_name/$res_dir/$filename") && !touch("$this->path/$view_name/$res_dir/$filename")) {
            echo "Unable to create files needed";
            return false;
        }
        foreach ($this->injections as $k=>$v) {
            if ($v[0] == $res_dir && $v[1] == $filename)
                $bool = 1;
        }
        if ($bool == 1)
            return false;
        else
            $this->injections[] = $res_dir . "/" . $filename;
        return true;
    }

    /**
     * public function addShared
     * @parameters string
     * @return bool
     */
    public function addShared(string $filename)
    {
        $bool = 0;
        if (!is_dir("$this->path/shared"))
            mkdir("$this->path/shared");
        if (!is_dir("$this->path/shared")) {
            echo "Unable to create directories needed";
            return false;
        }
        if (!file_exists("$this->path/shared/$filename")) {
            touch("$this->path/shared/$filename");
            if (!file_exists("$this->path/shared/$filename")) {
                echo "Unable to create files needed";
                return false;
            }
        }
        foreach ($this->injections as $k=>$v) {
            if ($v[0] == "shared" && $v[1] == $filename)
                $bool = 1;
        }
        if ($bool == 1)
            return false;
        else
            $this->injections[] = array("shared","$filename");
        return true;
    }

    /**
     * public function save
     * @parameters none
     * @return 1
     */
    public function save()
    {
        $fp = fopen($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json", "w");
        fwrite($fp, serialize($this));
        fclose($fp);
        return true;
    }

    /**
     * public function loadJSON
     * @parameters none
     * @return bool
     */
    public function loadThisJSON()
    {
        if (file_exists($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json") && filesize($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json") > 0)
            $fp = fopen($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json", "r");
        else
            return false;
        $json_context = fread($fp, filesize($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json"));
        $old = unserialize($json_context);
        $b = $old->mvc[$old->token];
        foreach ($b as $key => $val) {
            $this->mvc[$this->token]->sid->$key = $b->sid->$key;
        }
        return true;
    }

    /**
     * For user usage;
     * public function writeIndex
     * @parameters none
     * @return bool
     */
    private function writeThisIndex()
    {
        $buff = "<?php";
        $fp = fopen($this->token."/view/".$_COOKIE['PHPSESSID']."/index.php", "w");
        foreach ($this->injections as $k) {
            $vk = $k[0];
            $vv = $k[1];
            if ($vk == "shared") {
                $buff .= "\r\n\tinclude_once(\"../shared/$vv\");";
            }
            else {
                $buff .= "\r\n\tinclude_once(\"../index/$vk/$vv\");";
            }
        }
        $buff .= "?>\r\n";
        fwrite($fp, $buff);
        fclose($fp);
        return true;
    }

    /**
     * For domain usage;
     * public function writeIndex
     * @parameters none
     * @return bool
     */
    private function writeIndex()
    {
        $buff = "<?php";
        $fp = fopen("$this->token/index.php", "w");
        foreach ($this->injections as $k) {
            $vk = $k[0];
            $vv = $k[1];
            if ($vk == "shared") {
                $buff .= "\r\n\tinclude_once(\"view/shared/$vv\");";
            }
            else {
                $buff .= "\r\n\tinclude_once(\"view/index/$vk/$vv\");";
            }
        }
        $buff .= "?>\r\n";
        fwrite($fp, $buff);
        fclose($fp);
        return true;
    }

    /**
     * public function is_session_started
     * @return bool
     */
    function is_session_started()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
            return FALSE;
    }

    /**
     * For user usage;
     * public function configJSON
     * @parameters string
     * @return bool
     */
    public function configPageWrite(string $view_name = "index")
    {
        $fp = null;

        if (!is_dir($this->token."/view/".$_COOKIE['PHPSESSID']) && !mkdir($this->token."/view/".$_COOKIE['PHPSESSID']))
            echo "Unable to create directories";
        if ($view_name == "index") {
            touch($this->token."/view/".$_COOKIE['PHPSESSID']."/index.php");
            $this->writeIndex($this->md);
            return true;
        }
        else if (!file_exists($this->token."/view/".$_COOKIE['PHPSESSID']."/config.json"))
            echo "Unable to find files needed: ".$this->token."/view/".$_COOKIE['PHPSESSID']."/config.json";
        else if ($view_name != "index")
            $fp = fopen($this->token."/view/".$_COOKIE['PHPSESSID']."/index.php", "a");
        $dis = $this->loadThisJSON();
        $buff = "<?php\r\n";
        foreach ($this->injections as $k) {
            $vk = $k[0];
            $vv = $k[1];
            if ($vk == "shared") {
                $buff .= "include_once(\"../shared/$vv\");\r\n";
            }
            else if ($vk == "partials")
                $buff .= "include_once(\"../$view_name/$vk/$vv\");\r\n";
            else
                $buff .= "include_once(\"../view/".$_COOKIE['PHPSESSID']."/$vk/$vv\");\r\n";
        }
        $buff .= "?>\r\n";
        fwrite($fp, $buff);
        fclose($fp);
        return true;
    }


    /**
     * For domain usage;
     * public function writePage
     * @parameters string
     * @return bool
     */
    public function writePage(string $view_name)
    {
        $fp = null;
        if ($view_name == "index") {
            touch("$this->token/index.php");
            $this->writeIndex();
            return true;
        }
        else if (!dir_exists("$this->token/$this->md") && !mkdir("$this->path/$this->md"))
            echo "Unable to create directory needed";
        else if (!file_exists("$this->token/$this->md/index.php") && !touch("$this->token/$this->md/index.php"))
            echo "Unable to create files needed";
        if ($view_name != "index")
            $fp = fopen("$this->token/$this->md/index.php", "w");
        $buff = "<?php\r\n";
        foreach ($this->injections as $k) {
            $vk = $k[0];
            $vv = $k[1];
            if ($vk == "shared") {
                $buff .= "include_once(\"../shared/$vv\");\r\n";
            }
            else if ($vk == "partials")
                $buff .= "include_once(\"../$view_name/$vk/$vv\");\r\n";
            else
                $buff .= "include_once(\"../$view_name/$vk/$vv\");\r\n";
        }
        $buff .= "?>\r\n";
        fwrite($fp, $buff);
        fclose($fp);
        return true;
    }

    /**
     * public function removeDependency
     * @parameters string, string
     * @return bool
     */
    public function removeDependency(string $folder, string $partial)
    {
        $bool = 0;
        $k = [];
        foreach ($this->injections as $v) {
            if ($v != array($folder,$partial))
                $k = array_merge($k, array($v));
            else $bool = 1;
        }
        if ($bool == 1) {
            $this->injections = $k;
            return true;
        }
        return false;
    }

    /**
     * public function createAction
     * @parameters string
     * @return bool
     */
    public function createAction(string $action_name)
    {
        $this->actions[$this->copy] = new PageViews($this->token, $this->copy);
        $this->actions[$this->copy]->addPartial("index.php", $this->copy, $action_name);
        echo "<br><br><br>" . json_encode($this->actions);
        return true;
    }
}
