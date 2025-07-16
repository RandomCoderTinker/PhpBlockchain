# Test Files and Expected output

## TestWallet.php

Creates a wallet, Regenerates wallet from Mnemonic, Regenerates wallet from Private key, Hardcode sanity check.
Wallets can be added to services like metamask/trust wallet with the private key and Mnemonic.

```php
Expected Output:
🔐 Wallet Creation Test
=========================
⚙️  Generating new test wallet...

📦 Wallet Details:
  ➤ Address     : 0xcdafdb18f6b9d543a98fc6e50a5445fcf3a995b7
  ➤ Public Key  : 029506cdf3a2f75633d0d08dcc28007444ca5fedbfdd19722ec9e4285f7e526f47
  ➤ Private Key : 0xff17a0a1667be2ee96ae22df68ab8cd370dc4aa93f4f620cedf39d0de7b19b86
  ➤ Mnemonic    : panda fat arrest theme verify tool either curve south venture crowd vault

✅ Wallet successfully generated.

🔁 Regenerate from mnemonic...

🧪 Regenerated Wallet:
  ➤ Address     : 0xcdafdb18f6b9d543a98fc6e50a5445fcf3a995b7
  ➤ Public Key : 029506cdf3a2f75633d0d08dcc28007444ca5fedbfdd19722ec9e4285f7e526f47
  ➤ Private Key : 0xff17a0a1667be2ee96ae22df68ab8cd370dc4aa93f4f620cedf39d0de7b19b86
  ➤ Mnemonic : panda fat arrest theme verify tool either curve south venture crowd vault

🧪 Regenerated Wallet From Private key:
  ➤ Address     : 0xcdafdb18f6b9d543a98fc6e50a5445fcf3a995b7
  ➤ Public Key : 029506cdf3a2f75633d0d08dcc28007444ca5fedbfdd19722ec9e4285f7e526f47
  ➤ Private Key : 0xff17a0a1667be2ee96ae22df68ab8cd370dc4aa93f4f620cedf39d0de7b19b86
  ➤ Mnemonic : Dont Even Bother

🧠 Sanity Check From Hardcoded Mnemonic:
  ➤ Mnemonic    : label summer plug math hen cabin escape gadget decorate maximum crew enforce
  ➤ Address     : 0x2111dbd041e188db32d819d2e8f4ad6fba1279e5
  ➤ Public Key : 0385084b7dcce5eb682e3ea417e251ba7c0492d460bf105f7cefaa258f1c65681a
  ➤ Private Key : 0xcf65d5d6e60e54dd86464f2c7e321d381e3d1042ee7c5aae079fb130ef044aac

🎯 Restored from hardcoded mnemonic:
  ➤ Address     : 0x2111dbd041e188db32d819d2e8f4ad6fba1279e5
  ➤ Public Key : 0385084b7dcce5eb682e3ea417e251ba7c0492d460bf105f7cefaa258f1c65681a
  ➤ Private Key : 0xcf65d5d6e60e54dd86464f2c7e321d381e3d1042ee7c5aae079fb130ef044aac

✅ All tests passed.
```

## TestSignature.php

Test signing with a generated wallet.
Does a total of 2 tests on the following strings with the same wallet:

1. This is a test message that we signing and verifying
2. This is a test messаge that we signing and verifying

String two contains **Cyrillic** so fails the verification.

```php
Expected Output:
🔐 Signature Test Suite
========================

🔧 Generating test wallet...
Array
(
    [address] => 0xbc8af6d963dfd310ab3abe6a4c98d9fac459fc37
    [public_key] => 02ee769eb37a8009f6db23aa1007febff5b476ac0c918a228a49791ea410f1ec05
    [private_key] => 0xce63cfbe863cb35e2bbf7ef9b9fa599ebe1bf72b34904757f5a0a29adb3935b4
    [mnemonic] => evil floor tunnel bicycle debris accident digital vocal butter retire skull ten
    [derivation_path] => m/44'/60'/0'/0/0
)

✍️  Signing message...
Array
(
    [r] => 4af16af8102efc3d5d5fbc8c2a5d138f0a351888f28a103a1fa7e70c9980a530
    [s] => 6e21b61566eec421f69b932b491833aba29410db5175a7c83f7284283381f15b
    [v] => 27
    [signature] => 0x4af16af8102efc3d5d5fbc8c2a5d138f0a351888f28a103a1fa7e70c9980a5306e21b61566eec421f69b932b491833aba29410db5175a7c83f7284283381f15b1b
)

🔍 Verifying original message...
✔️  Signature valid? Yep ✅

🚨 Verifying tampered message (Cyrillic attack)...
✔️  Signature valid? Nay ❌

🔁 Recovering signer address from signature...
🔍 Recovered address: 0xbc8af6d963dfd310ab3abe6a4c98d9fac459fc37
🔗 Matches wallet? Yes ✅

🧪 Running fuzzy verification tests...

📃 Fuzz test result: 50 passed ✅, 0 failed ❌
⏱️  Done in 6.4781s
```

## TestLogger.php

Test to see if it can write in the correct directory and outputs basic logs

```php
📝 Logger Output Test
============================
🔧 Writing test logs...
[2025-07-15 22:22:29][error] This is a test error log
[2025-07-15 22:22:29][warning] This is a test warning log
[2025-07-15 22:22:29][info] This is a test info log
[2025-07-15 22:22:29][debug] This is a test debug log
[2025-07-15 22:22:29][success] This is a test success log

✅ Log entries written. Check your logger output destination (console, file, etc.).

📂 Recent Log Output (tail -10):
----------------------------
[2025-07-15 20:11:09][error] This is a test error log
[2025-07-15 20:11:09][warning] This is a test warning log
[2025-07-15 20:11:09][info] This is a test info log
[2025-07-15 20:11:09][debug] This is a test debug log
[2025-07-15 20:11:09][success] This is a test success log
[2025-07-15 22:22:29][error] This is a test error log
[2025-07-15 22:22:29][warning] This is a test warning log
[2025-07-15 22:22:29][info] This is a test info log
[2025-07-15 22:22:29][debug] This is a test debug log
[2025-07-15 22:22:29][success] This is a test success log
```

## TestMerkleTree.php

Tests creation of a Merkle Tree using fake TX's

```php
Expected Output:

🌲 Merkle Tree Test Suite
=============================
🔧 Building Merkle Tree from 5 transactions...
🔗 Merkle Root: 0x7570dc1be58415c8deab1d823b671e78224e4299d9a398f66e0896393a446fab

🔍 Verifying TX #0: "tx1: Alice -> Bob (5)"
  ➤ Leaf Hash : 0x9fd2c67b016247e3da4074d367c2fa5e64b38e2896334462b0572843cdf389cb
  ➤ Proof     : [{"hash":"0xce1735083ee9ce99bbb3f464adf524857f5106b9837ca58e720498e890990498","direction":"right"},{"hash":"0x10ef164dde069daf34ce82048950c6c72fd0612de3d82bcfd6e8b1ff54b767c5","direction":"right"},{"hash":"0x8c3b7e2b495fe041f5e230d8f0a1d8afa5f395018b43fc83c282f2017f84b8ca","direction":"right"}]
  ✅ Valid Proof

🔍 Verifying TX #1: "tx2: Bob -> Carol (3)"
  ➤ Leaf Hash : 0xce1735083ee9ce99bbb3f464adf524857f5106b9837ca58e720498e890990498
  ➤ Proof     : [{"hash":"0x9fd2c67b016247e3da4074d367c2fa5e64b38e2896334462b0572843cdf389cb","direction":"left"},{"hash":"0x10ef164dde069daf34ce82048950c6c72fd0612de3d82bcfd6e8b1ff54b767c5","direction":"right"},{"hash":"0x8c3b7e2b495fe041f5e230d8f0a1d8afa5f395018b43fc83c282f2017f84b8ca","direction":"right"}]
  ✅ Valid Proof

🔍 Verifying TX #2: "tx3: Dave -> Alice (2)"
  ➤ Leaf Hash : 0x22a8e34e52645f30786b6d25556a21d6f4bc7b0dff9ddfdcb7080ee6dd90f0a2
  ➤ Proof     : [{"hash":"0x23b94486cf5fefdf91fc5dffc7ef5075c9a101befcd81b184b8e5c561e88c21d","direction":"right"},{"hash":"0x9984485768af70c908c49418f85655b831fcfc441319fe82224b9324e5d700b1","direction":"left"},{"hash":"0x8c3b7e2b495fe041f5e230d8f0a1d8afa5f395018b43fc83c282f2017f84b8ca","direction":"right"}]
  ✅ Valid Proof

🔍 Verifying TX #3: "tx4: Eve -> Bob (7)"
  ➤ Leaf Hash : 0x23b94486cf5fefdf91fc5dffc7ef5075c9a101befcd81b184b8e5c561e88c21d
  ➤ Proof     : [{"hash":"0x22a8e34e52645f30786b6d25556a21d6f4bc7b0dff9ddfdcb7080ee6dd90f0a2","direction":"left"},{"hash":"0x9984485768af70c908c49418f85655b831fcfc441319fe82224b9324e5d700b1","direction":"left"},{"hash":"0x8c3b7e2b495fe041f5e230d8f0a1d8afa5f395018b43fc83c282f2017f84b8ca","direction":"right"}]
  ✅ Valid Proof

🔍 Verifying TX #4: "tx5: Bob -> Alice (0.1)"
  ➤ Leaf Hash : 0x35c640732c5ec8928d4da7d21b0d7525cb5eae6a057aeba18cc9079aa812f32c
  ➤ Proof     : [{"hash":"0x35c640732c5ec8928d4da7d21b0d7525cb5eae6a057aeba18cc9079aa812f32c","direction":"right"},{"hash":"0x3fd6bda8b3c70dad1fbadcf38c8581413f720a7807095036529cc2dbdad9cd4e","direction":"right"},{"hash":"0xa6587aa1d23a017ea64a93792e0a57bf3f0c2f846ff58ab85f37bed1344d3309","direction":"left"}]
  ✅ Valid Proof

🔁 Self-Test Check...
✅ MerkleTree::selfTest() passed

🏁 All Merkle tests passed!
```

## TestCryptoUtils.php

Tests the utils of converting tokens to and from WEI with basic math functions
Then runs a Fuzz Testing 50 times on random generated numbers.

```php
Expected Output:
� Running CryptoUnits Unit Tests
===============================
✅ PASS Convert 1.5 to wei
✅ PASS Convert 0.000000015 to wei
✅ PASS Convert 0 to wei
✅ PASS Handle invalid decimal input
✅ PASS Convert from wei 1.5e18
✅ PASS Convert from wei 0
✅ PASS Normalize base unit (no change)
✅ PASS Normalize decimal 1
✅ PASS Detect valid base unit
✅ PASS Detect invalid base unit
✅ PASS Add base units
✅ PASS Subtract base units
✅ PASS Multiply decimals to wei
✅ PASS Divide wei: 3e18 / 2e18
✅ PASS Divide by zero safely
✅ PASS Compare equal
✅ PASS Compare a > b
✅ PASS Compare a < b
===============================

🔍 Fuzz Testing CryptoUnits (50 iterations)
==========================================

� Fuzz Results: 50 passed, 0 failed
==========================================
```

## TestBlockCreation.php

Test creating of 3 blocks and adding then to the blockchain.

```php
Expected Output:

Currently working on this test.

```