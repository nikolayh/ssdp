<?php
class SSDP {
    /*
    **********************************************************************************************************************
    SSDP supports very simple searches without logical expressions, name-value pairs etc. Legal search type (ST) values are:
    ***********************************************************************************************************************
    ------------------------------------------------------------------------------------------------------------------------
    ssdp:all : to search all UPnP devices
    upnp:rootdevice: only root devices . Embedded devices will not respond
    uuid:device-uuid: search a device by vendor supplied unique id
    urn:schemas-upnp-org:device:deviceType- version: locates all devices of a given type (as defined by working committee)
    urn:schemas-upnp-org:service:serviceType- version: locate service of a given type
    ------------------------------------------------------------------------------------------------------------------------
    */

    private static $headers = "M-SEARCH * HTTP/1.1\r\nHost:239.255.255.250:1900\r\nST:upnp:rootdevice\r\nMan:\"ssdp:discover\"\r\nMX:3\r\n\r\n";
    private static $buffer = null;
    private static $_tmp = null;

    public static function getDevices() {

        var_dump(self::$headers);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>15, 'usec'=>10000));
        $send_ret = socket_sendto($socket, self::$headers, 1024, 0, '239.255.255.250', 1900);

        while(@socket_recvfrom($socket, self::$buffer, 1024, MSG_WAITALL, self::$_tmp, self::$_tmp)) {
            list($status, $St, $Usn, $Location, $Opt, $Nls, $MaxCache, $Server) = explode("\r\n", self::$buffer);
            $arrDevicesList[explode(':', $Usn)[2]] = [json_decode(json_encode(simplexml_load_string(file_get_contents(substr($Location, 9)))))];
        }

        socket_close($socket);
        return $arrDevicesList;
    }
}
var_dump(SSDP::getDevices());
