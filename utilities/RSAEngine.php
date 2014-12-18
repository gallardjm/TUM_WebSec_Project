<?php

include 'lib/BigInteger.php';


//inspired by http://phpseclib.sourceforge.net/
class RSAEngine {

	const CRYPT_RSA_EXPONENT = '65537';
	const CRYPT_RSA_SMALLEST_PRIME = '4096';
	const CRYPT_RSA_ASN1_INTEGER = 2;
	const CRYPT_RSA_ASN1_BITSTRING = 3;
	const CRYPT_RSA_ASN1_OCTETSTRING = 4;
	const CRYPT_RSA_ASN1_OBJECT = 6;
	const CRYPT_RSA_ASN1_SEQUENCE = 48;

	
	public function encrypt($text, $publicKey) {
		openssl_public_encrypt($text, $cypherText, $publicKey);
		return base64_encode($cypherText);
	}
	
	public function decrypt($cypherText, $privateKey) {
		openssl_private_decrypt(base64_decode($cypherText), $text, $privateKey);
		return $text;
	}
	
	public function generateGoodKeys($bits) {
		$config = array(
			"digest_alg" => "md5",
			"private_key_bits" => $bits,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		);

		// Create the private and public key
		$res = openssl_pkey_new($config);
		
		openssl_pkey_export($res, $privKey);
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];
		
		return array('privateKey' => $privKey, 'publicKey' => $pubKey);
	}
	
	//Generate keys, if $faulty one of the primes will be 5
	//use mt_rand() as random source
	public function generateFaultyKeys($bits) {
	
		$bits -= 2;
	
		if($bits > self::CRYPT_RSA_SMALLEST_PRIME)
			throw new InvalidArgumentException('too big');
	
		$one = new Math_BigInteger(1);
		$zero = new Math_BigInteger(0);
		$e = new Math_BigInteger(self::CRYPT_RSA_EXPONENT);
		
		extract($this->_generateMinMax($bits));
		$absoluteMin = $min;
		$num_primes = 2;
		$finalMax = $max;


		$generator = new Math_BigInteger();

		$n = $one->copy();
		$exponents = $coefficients = $primes = array();
		$lcm = array(
			'top' => $one->copy(),
			'bottom' => false
		);

		do {
			for ($i = 1; $i <= 2; $i++) {
				if ($i == 2) {
					list($min, $temp) = $absoluteMin->divide($n);
					if (!$temp->equals($zero)) {
						$min = $min->add($one); // ie. ceil()
					}
					$primes[$i] = $generator->randomPrime($min, $finalMax, false);
				} else {
					$primes[$i] = new Math_BigInteger(5); //faulty prime
				}				

				$n = $n->multiply($primes[$i]);

				$temp = $primes[$i]->subtract($one);

				// textbook RSA implementations use Euler's totient function instead of the least common multiple.
				// see http://en.wikipedia.org/wiki/Euler%27s_totient_function
				$lcm['top'] = $lcm['top']->multiply($temp);
				$lcm['bottom'] = $lcm['bottom'] === false ? $temp : $lcm['bottom']->gcd($temp);

				$exponents[$i] = $e->modInverse($temp);
			}

			list($temp) = $lcm['top']->divide($lcm['bottom']);
			$gcd = $temp->gcd($e);
		} while (!$gcd->equals($one));

		$d = $e->modInverse($temp);

		$coefficients[2] = $primes[2]->modInverse($primes[1]);

		// from <http://tools.ietf.org/html/rfc3447#appendix-A.1.2>:
		// RSAPrivateKey ::= SEQUENCE {
		//	   version			 Version,
		//	   modulus			 INTEGER,  -- n
		//	   publicExponent	 INTEGER,  -- e
		//	   privateExponent	 INTEGER,  -- d
		//	   prime1			 INTEGER,  -- p
		//	   prime2			 INTEGER,  -- q
		//	   exponent1		 INTEGER,  -- d mod (p-1)
		//	   exponent2		 INTEGER,  -- d mod (q-1)
		//	   coefficient		 INTEGER,  -- (inverse of q) mod p
		//	   otherPrimeInfos	 OtherPrimeInfos OPTIONAL
		// }
		
		return array(
			'privateKey' => $this->_convertPrivateKey($n, $e, $d, $primes, $exponents, $coefficients),
			'publicKey'	 => $this->_convertPublicKey($n, $e),
		);
	}
	
	/**
	 * Generates the smallest and largest numbers requiring $bits bits
	 *
	 * @access private
	 * @param Integer $bits
	 * @return Array
	 */
	private function _generateMinMax($bits)
	{
		$bytes = $bits >> 3;
		$min = str_repeat(chr(0), $bytes);
		$max = str_repeat(chr(0xFF), $bytes);
		$msb = $bits & 7;
		if ($msb) {
			$min = chr(1 << ($msb - 1)) . $min;
			$max = chr((1 << $msb) - 1) . $max;
		} else {
			$min[0] = chr(0x80);
		}

		return array(
			'min' => new Math_BigInteger($min, 256),
			'max' => new Math_BigInteger($max, 256)
		);
	}
	
	/**
	 * Convert a private key to the appropriate format.
	 *
	 * @access private
	 * @see setPrivateKeyFormat()
	 * @param String $RSAPrivateKey
	 * @return String
	 */
	private function _convertPrivateKey($n, $e, $d, $primes, $exponents, $coefficients)
	{
		$signed = true;
		$num_primes = count($primes);
		$raw = array(
			'version' => $num_primes == 2 ? chr(0) : chr(1), // two-prime vs. multi
			'modulus' => $n->toBytes($signed),
			'publicExponent' => $e->toBytes($signed),
			'privateExponent' => $d->toBytes($signed),
			'prime1' => $primes[1]->toBytes($signed),
			'prime2' => $primes[2]->toBytes($signed),
			'exponent1' => $exponents[1]->toBytes($signed),
			'exponent2' => $exponents[2]->toBytes($signed),
			'coefficient' => $coefficients[2]->toBytes($signed)
		);

		// eg. CRYPT_RSA_PRIVATE_FORMAT_PKCS1
		$components = array();
		foreach ($raw as $name => $value) {
			$components[$name] = pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($value)), $value);
		}

		$RSAPrivateKey = implode('', $components);

		if ($num_primes > 2) {
			$OtherPrimeInfos = '';
			for ($i = 3; $i <= $num_primes; $i++) {
				// OtherPrimeInfos ::= SEQUENCE SIZE(1..MAX) OF OtherPrimeInfo
				//
				// OtherPrimeInfo ::= SEQUENCE {
				//	   prime			 INTEGER,  -- ri
				//	   exponent			 INTEGER,  -- di
				//	   coefficient		 INTEGER   -- ti
				// }
				$OtherPrimeInfo = pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($primes[$i]->toBytes(true))), $primes[$i]->toBytes(true));
				$OtherPrimeInfo.= pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($exponents[$i]->toBytes(true))), $exponents[$i]->toBytes(true));
				$OtherPrimeInfo.= pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($coefficients[$i]->toBytes(true))), $coefficients[$i]->toBytes(true));
				$OtherPrimeInfos.= pack('Ca*a*', self::CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($OtherPrimeInfo)), $OtherPrimeInfo);
			}
			$RSAPrivateKey.= pack('Ca*a*', self::CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($OtherPrimeInfos)), $OtherPrimeInfos);
		}

		$RSAPrivateKey = pack('Ca*a*', self::CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($RSAPrivateKey)), $RSAPrivateKey);
				
		$RSAPrivateKey = "-----BEGIN RSA PRIVATE KEY-----\r\n" .
						 chunk_split(base64_encode($RSAPrivateKey), 64) .
						 '-----END RSA PRIVATE KEY-----';

		 return $RSAPrivateKey;
		
	}

	/**
	 * Convert a public key to the appropriate format
	 *
	 * @access private
	 * @see setPublicKeyFormat()
	 * @param String $RSAPrivateKey
	 * @return String
	 */
	private function _convertPublicKey($n, $e)
	{
		$signed = true;
		$modulus = $n->toBytes($signed);
		$publicExponent = $e->toBytes($signed);

		 // eg. CRYPT_RSA_PUBLIC_FORMAT_PKCS1_RAW or CRYPT_RSA_PUBLIC_FORMAT_PKCS1
		// from <http://tools.ietf.org/html/rfc3447#appendix-A.1.1>:
		// RSAPublicKey ::= SEQUENCE {
		//	   modulus			 INTEGER,  -- n
		//	   publicExponent	 INTEGER   -- e
		// }
		$components = array(
			'modulus' => pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($modulus)), $modulus),
			'publicExponent' => pack('Ca*a*', self::CRYPT_RSA_ASN1_INTEGER, $this->_encodeLength(strlen($publicExponent)), $publicExponent)
		);

		$RSAPublicKey = pack('Ca*a*a*',
			self::CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
			$components['modulus'], $components['publicExponent']
		);
	   
		// sequence(oid(1.2.840.113549.1.1.1), null)) = rsaEncryption.
		$rsaOID = pack('H*', '300d06092a864886f70d0101010500'); // hex version of MA0GCSqGSIb3DQEBAQUA
		$RSAPublicKey = chr(0) . $RSAPublicKey;
		$RSAPublicKey = chr(3) . $this->_encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;
		$RSAPublicKey = pack('Ca*a*',
			self::CRYPT_RSA_ASN1_SEQUENCE, $this->_encodeLength(strlen($rsaOID . $RSAPublicKey)), $rsaOID . $RSAPublicKey
		);

		$RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
						chunk_split(base64_encode($RSAPublicKey), 64) .
						'-----END PUBLIC KEY-----';
				

		return $RSAPublicKey;
	}
	
	private function _encodeLength($length)
	{
		if ($length <= 0x7F) {
			return chr($length);
		}

		$temp = ltrim(pack('N', $length), chr(0));
		return pack('Ca*', 0x80 | strlen($temp), $temp);
	}
}

?>