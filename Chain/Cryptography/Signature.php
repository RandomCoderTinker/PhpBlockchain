<?php

namespace Chain\Cryptography;

use kornrunner\Keccak;
use kornrunner\Secp256k1;
use Mdanter\Ecc\Crypto\Signature\Signature as EccSignature;
use Mdanter\Ecc\EccFactory;

class Signature
{

    /**
     * Sign a UTF-8 message using Ethereum’s “personal” signing scheme.
     *
     * Steps:
     *   1) Keccak-256 hash of the message
     *   2) ECDSA/secp256k1 signing → (r, s, v)
     *   3) Concatenates and returns signature as 0x⟪r(32)⟫⟪s(32)⟫⟪v(1)⟫
     *
     * @param string $message        The plain UTF-8 message to sign.
     * @param string $privateKeyHex  The private key in hex format (without 0x).
     *
     * @return array{
     *     r: string,
     *     s: string,
     *     v: int,
     *     signature: string
     * } Returns r, s, v and the full signature string (0x-prefixed).
     *
     * @throws \Exception If signing fails at any stage.
     */
    public static function sign(string $message, string $privateKeyHex): array
    {
        // 1) hash it
        $msgHash = Keccak::hash($message, 256);

        // 2) ask secp256k1 to sign
        $secp = new Secp256k1();
        $sig = $secp->sign($msgHash, $privateKeyHex);

        // r, s are GMP internally, turn into 64-char hex
        $r = str_pad(gmp_strval($sig->getR(), 16), 64, '0', STR_PAD_LEFT);
        $s = str_pad(gmp_strval($sig->getS(), 16), 64, '0', STR_PAD_LEFT);

        // recoveryParam is 0 or 1; add 27 per EIP-155 style
        $v = $sig->getRecoveryParam() + 27;
        $vHex = str_pad(dechex($v), 2, '0', STR_PAD_LEFT);

        return [
            'r' => $r,
            's' => $s,
            'v' => $v,
            'signature' => '0x' . $r . $s . $vHex
        ];

    }


    /**
     * Verifies an Ethereum-style signature against a UTF-8 message.
     *
     * The signature must be in the format: 0x⟪r(32)⟫⟪s(32)⟫⟪v(1)⟫
     *
     * @param string $message        The original UTF-8 message that was signed.
     * @param string $signatureHex   The hex-encoded signature (130 chars or 0x-prefixed).
     * @param string $publicKeyHex   The public key in hex format (without 0x).
     *
     * @return bool True if the signature is valid, false otherwise.
     */
    public static function verify(string $message, string $signatureHex, string $publicKeyHex): bool
    {
        // strip 0x
        $sig = preg_replace('#^0x#i', '', $signatureHex);
        if (strlen($sig) !== 130) {
            return false;
        }

        // split out r, s, v
        $rHex = substr($sig, 0, 64);
        $sHex = substr($sig, 64, 64);
        // v = hexdec(substr($sig, 128, 2)) — we don’t actually need it for raw verify

        // rebuild the SignatureInterface from r & s
        $rGmp = gmp_init($rHex, 16);
        $sGmp = gmp_init($sHex, 16);
        $signatureObj = new EccSignature($rGmp, $sGmp);

        // re-hash the message
        $msgHash = Keccak::hash($message, 256);

        // verify via secp256k1
        $secp = new Secp256k1();
        return $secp->verify($msgHash, $signatureObj, $publicKeyHex);
    }


    /**
     * Recovers the Ethereum address that signed a given UTF-8 message.
     *
     * Uses the signature to reconstruct the public key, then derives
     * the Ethereum address by Keccak-256 hashing the public key and taking the last 20 bytes.
     *
     * @param string $message        The original signed message.
     * @param string $signatureHex   The signature in hex format (130 chars or 0x-prefixed).
     *
     * @return string|null The recovered Ethereum address (0x-prefixed), or null if recovery fails.
     */
    public static function recoverAddress(string $message, string $signatureHex): ?string
    {
        $sig = preg_replace('#^0x#i', '', $signatureHex);
        if (strlen($sig) !== 130) {
            return null;
        }

        $rHex = substr($sig, 0, 64);
        $sHex = substr($sig, 64, 64);
        $vHex = substr($sig, 128, 2);

        $r = gmp_init($rHex, 16);
        $s = gmp_init($sHex, 16);
        $v = hexdec($vHex);

        $recoveryParam = $v - 27;
        if (!in_array($recoveryParam, [0, 1])) return null;

        $adapter = EccFactory::getAdapter();
        $curve = EccFactory::getSecgCurves()->curve256k1();
        $generator = EccFactory::getSecgCurves()->generator256k1();
        $n = $generator->getOrder();

        $e = gmp_init(Keccak::hash($message, 256), 16);

        // Reconstruct R point from r and v
        $x = $r;
        $prime = $curve->getPrime();

        // Solve y^2 = x^3 + ax + b mod p
        $a = $curve->getA();
        $b = $curve->getB();
        $alpha = gmp_mod(gmp_add(gmp_add(gmp_pow($x, 3), gmp_mul($a, $x)), $b), $prime);
        $beta = gmp_powm($alpha, gmp_div(gmp_add($prime, 1), 4), $prime); // √alpha mod p

        // Select the correct y based on recoveryParam (even/odd)
        $y = (gmp_cmp(gmp_mod($beta, 2), $recoveryParam) === 0) ? $beta : gmp_sub($prime, $beta);

        // Create point R
        $R = $curve->getPoint($x, $y);

        // Q = r^-1 * (sR - eG)
        $rInv = gmp_invert($r, $n);
        $sR = $R->mul($s);
        $eG = $generator->mul($e);
        $negY = gmp_mod(gmp_neg($eG->getY()), $curve->getPrime());
        $eGneg = $curve->getPoint($eG->getX(), $negY);
        $Q = $sR->add($eGneg)->mul($rInv);

        if (!$Q) return null;

        $xHex = str_pad(gmp_strval($Q->getX(), 16), 64, '0', STR_PAD_LEFT);
        $yHex = str_pad(gmp_strval($Q->getY(), 16), 64, '0', STR_PAD_LEFT);
        $pubKeyHex = $xHex . $yHex; // Fixed: Removed '04' prefix

        $keccak = Keccak::hash(hex2bin($pubKeyHex), 256);
        return '0x' . substr($keccak, 24);
    }


}