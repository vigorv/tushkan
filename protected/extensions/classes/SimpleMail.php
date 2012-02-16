<?php
DEFINE('HOSTNAME', getenv('HOSTNAME'));
class SimpleMail
{
	var $to		= "";
	var $from	= "";
	var $cc		= "";
	var $bcc	= "";
	var $subject	= "";
	var $TextBody	= "";
	var $TextBodyHTML= "";
	var $Charset	= "";
	var $message_array=array();
	var $partnum	= 0;
	var $hdfields	= array();
	var $returnPath = "";

	function simpleMail()
	{
	  $this->to		= "";
	  $this->from		= "";
	  $this->sublect	= "";
	  $this->TextBody	= "";
	  $this->TextBodyHTML	= "";
	  $this->Charset	= "utf-8";
	  $this->message_array	= array();
	  $this->cc		= "";
	  $this->bcc		= "";
	  $this->partnum	= 0;
	}

	function setReturnPath($val="")
	{
		$val = $this->extractEmail($val);
		if ($this->isEmail($val))
		{
			$this->returnPath = $val;
		}
	}

	/**
	 * проверка валидности адреса email (или списка адресов указанных через запятую)
	 *
	 * @param string $email
	 * @return boolean
	 */
	function isEmail($email)
	{
		$ems = preg_split ("/[\s,]+/", $email);
		foreach ($ems as $e)
		{
			$ptrn="/[a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\@[a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\.[a-zA-Z0-9]+/";
			if (!preg_match($ptrn, $e))
				return false;
		}
		return true;
	}

	/**
	 * извлечь адрес email из строки
	 *
	 * @param string $email
	 * @return string
	 */
	function extractEmail($email)
	{
		$ptrn="/[a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\@[a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\.[a-zA-Z0-9]+/";
		$matches = array();
		if (!preg_match($ptrn, $email, $matches, PREG_OFFSET_CAPTURE))
			return '';
		else
		{
			return $matches[0][0];
		}
	}

	function splitByEmail($str)
	{
		$ptrn="/<([a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\@[a-zA-Z0-9]+?[a-zA-Z0-9\_\-\.]*\.[a-zA-Z0-9]+)>[\s,]*/";
		$matches = preg_split($ptrn, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
		$str = '';
		$emails = array();
		$email = '';
		if (!empty($matches))
		{
			for($i = 0; $i < count($matches); $i++)
			{
				if ($this->isEmail($matches[$i]))
				{
					if (empty($email))
						$email .= $matches[$i];
					else
						$email .= ' <' . $matches[$i] . '>';
					$emails[] = $email;
					$email = '';
				}
				else
					$email = '=?' . $this->Charset . '?B?'.base64_encode($matches[$i]).'?=';
			}
		}
		return implode (', ', $emails);
	}

	function setTo($val="")
	{
		$this->to = $this->splitByEmail($val);
	}

	function setFrom($val="")
	{
		$this->setReturnPath($val);
		$this->from = $this->splitByEmail($val);
	}

	function setCc($val="")
	{
		$this->cc = $this->splitByEmail($val);
	}

	function setBcc($val="")
	{
		$this->bcc = $this->splitByEmail($val);
	}

	function setSubject($val="")
	{
		$this->subject= '=?' . $this->Charset . '?B?'.base64_encode($val).'?=';
	}

	function transCRLF($s)
	{
		$t = array(chr(13) => "");
		$s = strtr($s, $t);
		$t = array('&#34;' => '"');
		$s = strtr($s, $t);
	  	$t = array('&#92;' => chr(92));
		$s = strtr($s, $t);
		return $s;
	}

	function setTextBody($val="")
	{
		$this->TextBody= $this->transCRLF($val);

		$this->message_array[$this->partnum]['content_type'] = 'text/plain; charset='.$this->Charset;
		$this->message_array[$this->partnum]['not64'] = TRUE;
		$this->message_array[$this->partnum]['data'] = $this->TextBody;

                $this->partnum++;
	}

	function setTextBodyHTML($val="")
	{
		$this->TextBodyHTML= $val;

		$this->message_array[$this->partnum]['content_type'] = 'text/html; charset='.$this->Charset;
		$this->message_array[$this->partnum]['not64'] = TRUE;
		$this->message_array[$this->partnum]['data'] = $this->TextBodyHTML;
                $this->partnum++;
	}

	function setCharset($val="")
	{
		$this->Charset= $val;
	}

	function AttachFile($filename,$name)
	{
		$this->message_array[$this->partnum]['content_type'] = 'image/jpeg';
		$this->message_array[$this->partnum]['filename'] = $name;
		$this->message_array[$this->partnum]['data'] = $this->readallfile($filename);
		$this->message_array[$this->partnum]['headers'] = array('X-Sent-By' => $this->from, 'X-mailer' => 'X-mailer');
                $this->partnum++;
	}

	function readallfile($filename)
	{
		$buf = '';
		if (file_exists($filename) && (filesize($filename)>0))
		{
		  $f = fopen($filename, 'r');
		  $buf = fread ($f, filesize ($filename));
		  fclose($f);
		}
		return $buf;
	}

	function getmsgid()
	{
		return '<'.'msg-'.str_replace(' ','.',microtime()).'@'.HOSTNAME.'>';
	}

	function getboundary()
	{
		return '-'.'msg-'.str_replace(' ','.',microtime());
	}

	function addhdfield($name, $value)
	{
	  $this->hdfields[]="$name: $value\n";
	}

	function send()
	{
		if (!Yii::app()->params['tushkan']['sendMail'])
			return;

	  if (empty($this->returnPath))
	  {
	  	$this->returnPath = $this->from;
	  }
	  $boundary = $this->getboundary();
	  $buf='';
	  while(list(, $chunk) = each($this->message_array))
	  {
	    $headers=array();
	    $data='';
	    if (!@$chunk['not64'])
	    {
	      $headers['Content-ID'] = $this->getmsgid();
	      $headers['Content-Transfer-Encoding'] = 'BASE64';
	      if (strlen($chunk['filename']))
	      {
	        $headers['Content-Type'] = $chunk['content_type'].'; name="'.$chunk['filename'].'"';
	        $headers['Content-Description'] = '';
	        $headers['Content-Disposition'] = 'attachment; filename="'.$chunk['filename'].'"';
	      }
	      else $headers['Content-Type'] = $chunk['content_type'];
	      $data = chunk_split(base64_encode($chunk['data']),60,"\n");
	    }
	    else
	    {
	      $headers['Content-Type'] = $chunk['content_type'];
	      $data = $chunk['data'] . "\n";
	    }

	    if (is_array(@$chunk['headers']) && count(@$chunk['headers']))
	    {
	      while(list($key, $val) = each($chunk['headers']))
	        $headers[$key] = $val;
	    }

	    $buf .= '--' . $boundary. "\n";
	    while(list($key, $val) = each($headers))
	      $buf .= $key.': '.$val."\n";
	    $buf .= "\n";
	    $buf .= $data;
	  }

	  $buf .= '--'.$boundary.'--';

	  $retarray = array(
	    0 => $buf,
	    1 => "MIME-Version: 1.0"."\n"."Content-Type: MULTIPART/MIXED;"."\n".' BOUNDARY="'.$boundary.'"'."\n"."X-Generated-By: http://{$_SERVER['HTTP_HOST']}"."\n"."X-Return-Path: ".$this->returnPath."\n",
	    2 => array('MIME-Version: 1.0','Content-Type: MULTIPART/MIXED;'."\n".'  BOUNDARY="'.$boundary.'"',"X-Generated-By: http://{$_SERVER['HTTP_HOST']}"."\n"."X-Return-Path: ".$this->returnPath."\n")
	  );

	  $ccbcc='';
	  if ($this->cc<>'') $ccbcc.='CC: '.$this->cc."\n";
	  if ($this->bcc<>'') $ccbcc.="BCC: ".$this->bcc."\n";
	  if (($this->partnum==1)&&($this->TextBody<>''))
	    mail($this->to, $this->subject, $this->TextBody, "From: ".$this->from."\n".$ccbcc.implode('', $this->hdfields)."X-Generated-By: http://{$_SERVER['HTTP_HOST']}"."\n"."X-Return-Path: ".$this->returnPath."\n"."Content-Type: text/plain; charset=".$this->Charset, "-f".$this->returnPath);
	  else
	    mail($this->to, $this->subject, $retarray[0], "From: ".$this->from."\n".$retarray[1]."\n".implode('', $this->hdfields)."X-Generated-By: http://{$_SERVER['HTTP_HOST']}"."\n"."X-Return-Path: ".$this->returnPath."\n".$ccbcc, "-f".$this->returnPath);
          $this->partnum=0;
        }
} // END OF class MAIL()

