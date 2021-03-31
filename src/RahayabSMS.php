<?php


namespace MrHMH\SmsRahyab;


class RahayabSMS
{

    private $company;
    private $host;
    private $port;
    private $username;
    private $password;
    private $sender;

    public function __construct()
    {
        $this->company = config('services.sms_rahyab.company');
        $this->host = config('services.sms_rahyab.host');
        $this->port = config('services.sms_rahyab.port');
        $this->username = config('services.sms_rahyab.username');
        $this->password = config('services.sms_rahyab.password');
        $this->sender = config('services.sms_rahyab.sender');
    }

    protected function C2Unicode($uMessage)
    {
//        -@curl_setopt($ch, CURLOPT_HTTPHEADER, array("authorization:Basic " . $this->Base64Encode($this->username . ":" . $this->password)));
        $ret = "";
        $i = 0;
        while ($i < strlen($uMessage)) {
            $hexstr = "";
            if ($i + 1 < strlen($uMessage)) {
                if (mb_substr($uMessage, $i, 1) == "&") {
                    if ($i + 2 < strlen($uMessage) && substr($uMessage, $i + 1, 1) == "#") {
                        $i += 2;
                        $semipos = strrpos($uMessage, ';', $i);
                        if ($semipos > $i) {
                            $hexstr = sprintf("%04x", substr($uMessage, $i, 5));
                            if (substr($uMessage, $i + 3, 1) == ";")
                                $i += 4;
                            else if (substr($uMessage, $i + 4, 1) == ";")
                                $i += 5;
                            else if (substr($uMessage, $i + 5, 1) == ";")
                                $i += 6;
                            else
                                $i += 7;
                        } else {
                            $hexstr = sprintf("%04x", $this->uniord("&"));
                            $hexstr .= sprintf("%04x", $this->uniord("#"));
                        }
                    }
                } else {
                    $hexstr = sprintf("%04x", $this->uniord(substr($uMessage, $i, 1)));
                    $i++;
                }
            } else {
                $hexstr = sprintf("%04x", $this->uniord(substr($uMessage, $i, 1)));
                $i++;
            }

            $ret .= $hexstr;
        }
        return $ret;
    }

    protected function CorrectNumber($uNumber)
    {

        $uNumber = Trim($uNumber);
        $ret = $uNumber;
        if (substr($uNumber, 0, 4) == "0098") {
            $ret = substr($uNumber, 4);
            $uNumber = $ret;
        }

        if (substr($uNumber, 0, 3) == "098") {
            $ret = substr($uNumber, 3);
            $uNumber = $ret;
        }

        if (substr($uNumber, 0, 3) == "+98") {
            $ret = substr($uNumber, 3);
            $uNumber = $ret;
        }

        if (substr($uNumber, 0, 2) == "98") {
            $ret = substr($uNumber, 2);
            $uNumber = $ret;
        }

        if (substr($uNumber, 0, 1) == "0") {
            $ret = substr($uNumber, 1);
            $uNumber = $ret;
        }

        return "+98" . $ret;
    }

    protected function MyASC($OneChar)
    {
        if ($OneChar == "") {
            return 0;
        } else {

            return ord($OneChar);
        }
    }

    protected function uniord($c)
    {
        $ud = 0;
        if (ord($c[0]) >= 0 && ord($c[0]) <= 127)
            $ud = ord($c[0]);
        if (ord($c[0]) >= 192 && ord($c[0]) <= 223)
            $ud = (ord($c[0]) - 192) * 64 + (ord($c[1]) - 128);
        if (ord($c[0]) >= 224 && ord($c[0]) <= 239)
            $ud = (ord($c[0]) - 224) * 4096 + (ord($c[1]) - 128) * 64 + (ord($c[2]) - 128);
        if (ord($c[0]) >= 240 && ord($c[0]) <= 247)
            $ud = (ord($c[0]) - 240) * 262144 + (ord($c[1]) - 128) * 4096 + (ord($c[2]) - 128) * 64 + (ord($c[3]) - 128);
        if (ord($c[0]) >= 248 && ord($c[0]) <= 251)
            $ud = (ord($c[0]) - 248) * 16777216 + (ord($c[1]) - 128) * 262144 + (ord($c[2]) - 128) * 4096 + (ord($c[3]) - 128) * 64 + (ord($c[4]) - 128);
        if (ord($c[0]) >= 252 && ord($c[0]) <= 253)
            $ud = (ord($c[0]) - 252) * 1073741824 + (ord($c[1]) - 128) * 16777216 + (ord($c[2]) - 128) * 262144 + (ord($c[3]) - 128) * 4096 + (ord($c[4]) - 128) * 64 + (ord($c[5]) - 128);
        if (ord($c[0]) >= 254 && ord($c[0]) <= 255) //error
            $ud = false;
        return $ud;
    }

    protected function Base64Encode($inData)
    {
        return base64_encode($inData);
    }

    protected function DecodeUCS2($Content)
    {

        $hextext = $Content;

        $ret = "";
        for ($i = 0; $i <= mb_strlen($hextext, "utf-8") - 1; $i += 4) {

            $ret = $ret . $this->unichr(hexdec("&h" . mb_substr($hextext, $i, 4)));
        }
        return $ret;

    }

    protected function unichr($c)
    {
        if ($c <= 0x7F) {
            return chr($c);
        } else if ($c <= 0x7FF) {
            return chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
        } else if ($c <= 0xFFFF) {
            return chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
        } else if ($c <= 0x10FFFF) {
            return chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
        } else {
            return false;
        }
    }

    protected function run_command($username, $password, $xml)
    {
        $ch = curl_init();
        @curl_setopt($ch, CURLOPT_URL, 'http://' . $this->host . ':' . $this->port . '/CPSMSService/Access');
        @curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        @curl_setopt($ch, CURLOPT_POST, 1);
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        @curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        @curl_setopt($ch, CURLOPT_VERBOSE, 1);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array("authorization:Basic " . $this->Base64Encode($username . ":" . $password), 'Content-Type: text/xml', 'Connection: Keep-Alive'));
        @curl_setopt($ch, CURLOPT_HEADER, 0);
        @curl_setopt($ch, CURLOPT_COOKIEFILE, 1);

        $result = @curl_exec($ch);
        //$result = ""; //@curl_exec ( $ch );
        @curl_close($ch);

        return $result;
    }

    public function send_sms(array $number_array, array $note_array)
    {
        $i = 0;
        $smsid_arr = array();
        $perfix = $this->company . "+" . date("YmdHis");

        foreach ($number_array as $number) {
            if (isset ($note_array [$number])) {
                $new_message_note = $note_array [$number];
            } elseif (isset ($note_array [$i])) {
                $new_message_note = $note_array [$i];
            } else {
                print 'Sms content is empty.';
                die ();
                exit ();
            }

            $BatchID = $perfix . sprintf("%03d", rand(0, 999));
            $txt = "";
            $txt = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $txt .= "<!DOCTYPE smsBatch PUBLIC \"-//PERVASIVE//DTD CPAS 1.0//EN\" \"http://www.ubicomp.ir/dtd/Cpas.dtd/\">\n";
            $txt .= "<smsBatch company=\"" . $this->company . "\" batchID=\"" . $BatchID . "\">\n";

            preg_match('/([^\x{0080}-\x{FFFF}])/u', $new_message_note) ? $vFarsi = 1 : $vFarsi = 2;
            if ($vFarsi == 1) {
                $txt .= "<sms binary=\"true\" dcs=\"8\">\n";
                $vMessage = $this->C2Unicode($new_message_note);
            } else {
                $txt .= "<sms  binary=\"false\" dcs=\"0\">\n";
                $vMessage = $new_message_note;
            }

            $txt .= "<destAddr><![CDATA[" . $this->CorrectNumber($number) . "]]></destAddr>\n";
            $txt .= "<origAddr><![CDATA[" . $this->CorrectNumber($this->sender) . "]]></origAddr>\n";
            $txt .= "<message><![CDATA[" . $vMessage . "]]></message>\n";
            $txt .= "</sms>\n";
            $txt .= "</smsBatch>\n";

            $res = $this->run_command($this->username, $this->password, $txt);

            if (preg_match('#CHECK_OK#i', $res)) {
                $smsid_arr [$number] = $BatchID;
            } else {
                $smsid_arr [$number] = "Failed";
            }

            $i++;
        }

        return $smsid_arr;
    }

    public function send_batch_sms($username, $password, $sender, $number_array, $note)
    {
        $i = 0;
        $perfix = $this->company . "+" . date("YmdHis");

        if (!isset ($note)) {
            print 'Sms content is empty.';
            die ();
            exit ();
        }

        $BatchID = $perfix . sprintf("%03d", rand(0, 999));
        $txt = "";
        $txt = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $txt .= "<!DOCTYPE smsBatch PUBLIC \"-//PERVASIVE//DTD CPAS 1.0//EN\" \"http://www.ubicomp.ir/dtd/Cpas.dtd/\">\n";
        $txt .= "<smsBatch company=\"" . $this->company . "\" batchID=\"" . $BatchID . "\">\n";

        preg_match('/([^\x{0080}-\x{FFFF}])/u', $note) ? $vFarsi = 1 : $vFarsi = 2;
        if ($vFarsi == 1) {
            $txt .= "<sms binary=\"true\" dcs=\"8\">\n";
            $vMessage = $this->C2Unicode($note);
        } else {
            $txt .= "<sms  binary=\"false\" dcs=\"0\">\n";
            $vMessage = $note;
        }

        foreach ($number_array as $number) {
            $txt .= "<destAddr><![CDATA[" . $this->CorrectNumber($number) . "]]></destAddr>\n";
        }

        $txt .= "<origAddr><![CDATA[" . $this->CorrectNumber($sender) . "]]></origAddr>\n";
        $txt .= "<message><![CDATA[" . $vMessage . "]]></message>\n";
        $txt .= "</sms>\n";
        $txt .= "</smsBatch>\n";

        $res = $this->run_command($username, $password, $txt);

        if (preg_match('#CHECK_OK#i', $res)) {
            $smsid = $BatchID;
        } else {
            $smsid = "Failed";
        }

        return $smsid;
    }

    public function get_delivery($username, $password, $sender, $smsid)
    {
        if (is_array($smsid)) {
            $smsResp_arr = array();
            for ($i = 0; $i < count($smsid); $i++) {
                $smsResp_arr [$smsid [$i]] = $this->get_delivery($username, $password, $sender, $smsid [$i]);
            }
            return $smsResp_arr;
        } else {
            $StrXML = "<?xml version=\"1.0\"?>\n";
            $StrXML .= "<!DOCTYPE smsStatusPoll PUBLIC \"-//PERVASIVE//DTD CPAS 1.0//EN\" \"http://www.pervasive.ir/dtd/Cpas.dtd\" []>";
            $StrXML .= "<smsStatusPoll company=\"" . $this->company . "\"> ";
            $StrXML .= "<batch batchID=\"" . $smsid . "\" /> ";
            $StrXML .= "</smsStatusPoll>";
            $res = $this->run_command($username, $password, $StrXML);

            if (preg_match('#MT_DELIVERED#i', $res)) {
                $smsResp = 1;
            } else if (preg_match('#CHECK_OK#i', $res)) {
                $smsResp = 2;
            } else if (preg_match('#CHECK_ERROR#i', $res)) {
                $smsResp = 3;
            } else if (preg_match('#SMS_FAILED#i', $res)) {
                $smsResp = 4;
            } else {
                $smsResp = 0;
            }
            return $smsResp;
        }
    }

    public function get_delivery_phone($username, $password, $sender, $smsid, $phone)
    {
        $StrXML = "<?xml version=\"1.0\"?>\n";
        $StrXML .= "<!DOCTYPE smsStatusPoll PUBLIC \"-//PERVASIVE//DTD CPAS 1.0//EN\" \"http://www.pervasive.ir/dtd/Cpas.dtd\" []>";
        $StrXML .= "<smsStatusPoll company=\"" . $this->company . "\"> ";
        $StrXML .= "<batch batchID=\"" . $smsid . "\" /> ";
        $StrXML .= "</smsStatusPoll>";
        $res = $this->run_command($username, $password, $StrXML);
        $phonePos = strpos($res, $this->CorrectNumber($phone));
        if ($phonePos > 0) {
            $res = substr($res, $phonePos, 60);
        } else {
            $res = "";
        }
        if (preg_match('#MT_DELIVERED#i', $res)) {
            $smsResp = 1;
        } else if (preg_match('#CHECK_OK#i', $res)) {
            $smsResp = 2;
        } else if (preg_match('#CHECK_ERROR#i', $res)) {
            $smsResp = 3;
        } else if (preg_match('#SMS_FAILED#i', $res)) {
            $smsResp = 4;
        } else {
            $smsResp = 0;
        }
        return $smsResp;
    }

    public function get_cash($username, $password)
    {
        $StrXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $StrXML .= "<getUserBalance company=\"" . $this->company . "\" /> ";
        $data = $this->run_command($username, $password, $StrXML);
        $data = str_replace('<![CDATA[', '', $data);
        $data = str_replace(']]>', '', $data);
        $data = str_replace('(empty)', '', $data);
        if (preg_match('#<userBalance>(.*?)</userBalance>#simu', $data, $db_get)) {
            return trim($db_get [1]);
        } else {
            return 'no';
        }
    }

    public function receive($username, $password)
    {
        $StrXML = "<?xml version=\"1.0\"?>\n";
        $StrXML .= "<!DOCTYPE smsPoll PUBLIC \"\" \" http://www.pervasive.ir/dtd/Cpas.dtd\" []>";
        $StrXML .= "<smsPoll company=\"" . $this->company . "\"/> ";
        $data = $this->run_command($username, $password, $StrXML);
        $sms_arr = array();

        $data = str_replace('<![CDATA[', '', $data);
        $data = str_replace(']]>', '', $data);
        $data = str_replace('(empty)', '', $data);

        @preg_match_all('#<batch batchID="(.*?)">(.*?)</batch>#simu', $data, $matches);
        for ($intI = 0; $intI < count($matches [1]); $intI++) {
            $sms_arr [$intI] ['id'] = $matches [1] [$intI];
            $data_halghe = $matches [2] [$intI];
            if (preg_match('#<origAddr>(.*?)</origAddr>#i', $data_halghe, $db_get)) {
                $sms_arr [$intI] ['from'] = '0' . $db_get [1];
            }
            if (preg_match('#<destNumber>(.*?)</destNumber>#i', $data_halghe, $db_get)) {
                $sms_arr [$intI] ['to'] = $db_get [1];
            }
            if (preg_match('#<time>(.*?)</time>#i', $data_halghe, $db_get)) {
                $sms_arr [$intI] ['time'] = strtotime($db_get [1]);
            }
            if (preg_match('#<message>(.*?)</message>#i', $data_halghe, $db_get)) {
                if (preg_match('#<dcs>0</dcs>#i', $data_halghe)) {
                    $sms_arr [$intI] ['content'] = $db_get [1];
                } else {
                    $sms_arr [$intI] ['content'] = $this->DecodeUCS2($db_get [1]);

                }
            }
        }
        return $sms_arr;
    }

}
