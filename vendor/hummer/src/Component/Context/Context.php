<?php
namespace Hummer\Component\Context;

use Hummer\Component\Configure\Configure;
/**
 *  上下文
 **/
class Context{

    public static function getInst()
    {
        return end($GLOBALS['__SELF__CONTEXT']);
    }

    public static function makeInst()
    {
        if (!isset($GLOBALS['__SELF__CONTEXT'])) {
            $GLOBALS['__SELF__CONTEXT'] = array();
        }
        return $GLOBALS['__SELF__CONTEXT'][] = new static();
    }

    private $__aVarRegister__;
    public function registerMulti($aRegisterMap)
    {
        foreach ($aRegisterMap as $sK => $mV) {
            $this->__aVarRegister__[$sK] = true;
            $this->$sK = $mV;
        }
    }

    public function isRegister($sRegName)
    {
        return isset($this->__aVarRegister__[$sRegName]) && $this->__aVarRegister__[$sRegName];
    }

    private static $_aAllCFG = null;

    private $__aModuleConf__;

    public function __get($sVarName)
    {
        if (!$this->isRegister('Config')) {
            throw new \DomainException('[CTX] : Error');
        }
        $CFG = $this->Config;

        if (self::$_aAllCFG === null) self::$_aAllCFG = $CFG->get('module');

        foreach (self::$_aAllCFG as $aModule) {
            if (!isset($aModule['module']) || !isset($aModule['class'])) {
                throw new \DomainException('[CTX] : Error');
            }
            if ($aModule['module'] != $sVarName || $aModule['run_mode'] != $this->sRunMode) {
                continue;
            }
            $this->__aModuleConf__[$sVarName] = $aModule;
        }

        if (!isset($this->__aModuleConf__[$sVarName])) {
            throw new \DomainException('[CTX] : Error : no module');
        }
        $aModuleConfig = $this->__aModuleConf__[$sVarName];
        $aModuleConfig = Configure::parseRecursion($aModuleConfig, $CFG);
        if (!isset($aModuleConfig['params'])) {
            $aModuleConfig['params'] = array();
        }
        if (empty($aModuleConfig['params'])) {
            $Obj = new $aModuleConfig['class']();
        }else{
            $Ref = new \ReflectionClass($aModuleConfig['class']);
            $Obj = $Ref->newInstanceArgs($aModuleConfig['params']);
        }

        if (isset($aModuleConfig['packer'])) {
            $Obj = new Packer($Obj, $aModuleConfig['packer']);
        }
        $this->$sVarName = $Obj;#SAVE FOR NEXT
        return $Obj;
    }
}