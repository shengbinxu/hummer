<?php
namespace Hummer\Component\Route;

use Hummer\Component\Helper\Arr;

class Mode{

    public static function Http_Page(
        $REQ,
        $RES,
        $sControllerPath,
        $sControllerPre,
        $sActionPre
    ) {
        $sURL = self::_TrimInValidURI(Arr::get(parse_url($REQ->getRequestURI()),'path',''));
        $aURLPATH = explode('/', substr($sURL,1));
        #Action
        $sURIAction = array_pop($aURLPATH);
        $sURIAction = $sURIAction === '' ? 'default' : $sURIAction;
        $sAction    = $sActionPre . ucfirst($sURIAction);
        if (count($aURLPATH) === 0) {
            array_unshift($aURLPATH, 'main');
        }
        $sControllerName    = ucfirst(array_pop($aURLPATH));
        $sControllerDepth   = self::TrimEnd(implode('\\', $aURLPATH),'\\');
        $sControllerPathPre = sprintf('%s%s%s%s',
            $sControllerPath,
            $sControllerDepth,
            $sControllerPre ,
            $sControllerName
        );
        if($sRequestMethod=$REQ->getRequestMethod() !== 'GET'){
            $sControllerPathPre .= '_' . $sControllerPathPre;
        };
        $CallBack = new CallBack();
        $CallBack->setCBObject($sControllerPathPre, $sAction);

        return $CallBack;
    }

    protected static function TrimEnd($sV, $sSeperator='/')
    {
        $sV = trim($sV, $sSeperator) . $sSeperator;
        return $sV === $sSeperator ? '' : $sV;
    }

    private static function _TrimInValidURI($sURI)
    {
        while (strpos($sURI, '//')) {
            $sURI = str_replace('//','/', $sURI);
        }
        return $sURI;
    }
}