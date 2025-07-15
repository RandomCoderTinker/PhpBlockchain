<?php

namespace Chain\Wallet;

class WalletManager
{

    public function createWallet()
    {

        try {
            $mnemonic = Mnemonic::generate(); // Returns array of words
            $keyPair = KeyPair::fromMnemonic(implode(' ', $mnemonic), '');
            $address = $keyPair->getAddress();

            return [
                'address' => $address,
                'public_key' => $keyPair->getPublicKey(),
                'private_key' => $keyPair->getPrivateKey(),
                'mnemonic' => $mnemonic
            ];
        } catch (\Exception $e) {
            throw new \Exception("Failed to create wallet: " . $e->getMessage());
        }


    }

}