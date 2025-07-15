# Test Files and Expected output

## TestWallet.php

As of now, the mnemonic is incorrect and wont import to MetaMask Correctly

```php
Expected Output:
Array
(
    [address] => 0xeefe65fa9e7620f9c369e29161b1d976416bedd0
    [public_key] => 026201a272e63a67fcf393a356fd1f24bbd5d2a938cde97eb0cd054721cfa5664c
    [private_key] => eb3f94cdd2d9e1fa849c54829bfde38a501f3217a229d83c871d95e1be0c4aa1
    [mnemonic] => Array
        (
            [0] => argue
            [1] => screen
            [2] => bargain
            [3] => spell
            [4] => report
            [5] => tool
            [6] => salon
            [7] => venture
            [8] => amateur
            [9] => deposit
            [10] => frequent
            [11] => conduct
        )
)
```

## TestSignature.php

Test signing with a generated wallet.
Does a total of 2 tests on the following strings with the same wallet:

1. This is a test message that we signing and verifying
2. This is a test messаge that we signing and verifying

String two contains **Cyrillic** so fails the verification.

```php
Expected Output:
Array
(
    [address] => 0xe0481530d049251ed6b6bae8945d3ea1bcfe6994
    [public_key] => 03beaeb269c3f3667ed435f7dae1350cda5df090421f9fbc9ffbaaf6848cb08c50
    [private_key] => 7dcf41491d6bfbea35e5d2582381e34adf0349ec160a24adcf16321346411d07
    [mnemonic] => Array
        (
            [0] => destroy
            [1] => morning
            [2] => axis
            [3] => blast
            [4] => finger
            [5] => boss
            [6] => manage
            [7] => outside
            [8] => hedgehog
            [9] => mind
            [10] => sport
            [11] => ankle
        )

)
Array
(
    [r] => 1e1266aa7567cc3594209f60c36acd147d7d7dc815911c168dc51d738d5dad1c
    [s] => 17c196f8cb4efeea1eb5a49cd5e53c43431ab74dc686574373a76d5698f4839e
    [v] => 28
    [signature] => 0x1e1266aa7567cc3594209f60c36acd147d7d7dc815911c168dc51d738d5dad1c17c196f8cb4efeea1eb5a49cd5e53c43431ab74dc686574373a76d5698f4839e1c
)
Signature valid? Yep ✅
Signature valid? Nay ❌
0xe0481530d049251ed6b6bae8945d3ea1bcfe6994
```

## TestLogger.php

Test to see if it can write in the correct directory and outputs basic logs

```php
Expected Output:
[CURRENT_TIMESTAMP][error] This is an error
[CURRENT_TIMESTAMP][warning] This is an warning
[CURRENT_TIMESTAMP][info] This is an info
[CURRENT_TIMESTAMP][debug] This is a debug
[CURRENT_TIMESTAMP][success] This is a success
```

## TestMerkleTree.php

Tests creation of a Merkle Tree using fake TX's

```php
Expected Output:

🌴 Merkle Tree Test
----------------------
🫚 Merkle Root: 8c73137a6762685d6f30d9db0cfe719a72008cf13d464d4ce999d45b38d3d989

TX #0: "tx1: Alice -> Bob (5)"
  ➤ Leaf: 9fd2c67b016247e3da4074d367c2fa5e64b38e2896334462b0572843cdf389cb
  ➤ Proof: [{"hash":"ce1735083ee9ce99bbb3f464adf524857f5106b9837ca58e720498e890990498","direction":"right"},{"hash":"e49ef56493592305a2e9d852b614ed4122a79fbf1fd78358559b11f24195cd20","direction":"right"},{"hash":"209ef6adbb1be15dddeae5fa41bce6e24b35bd96bbd589f37384a892a5184d65","direction":"right"}]
  ✅ Verified

TX #1: "tx2: Bob -> Carol (3)"
  ➤ Leaf: ce1735083ee9ce99bbb3f464adf524857f5106b9837ca58e720498e890990498
  ➤ Proof: [{"hash":"9fd2c67b016247e3da4074d367c2fa5e64b38e2896334462b0572843cdf389cb","direction":"left"},{"hash":"e49ef56493592305a2e9d852b614ed4122a79fbf1fd78358559b11f24195cd20","direction":"right"},{"hash":"209ef6adbb1be15dddeae5fa41bce6e24b35bd96bbd589f37384a892a5184d65","direction":"right"}]
  ✅ Verified

TX #2: "tx3: Dave -> Alice (2)"
  ➤ Leaf: 22a8e34e52645f30786b6d25556a21d6f4bc7b0dff9ddfdcb7080ee6dd90f0a2
  ➤ Proof: [{"hash":"23b94486cf5fefdf91fc5dffc7ef5075c9a101befcd81b184b8e5c561e88c21d","direction":"right"},{"hash":"379fa02fb915733c2969a6f292a09fb22cdcd0b6ef7377f2b621172584135767","direction":"left"},{"hash":"209ef6adbb1be15dddeae5fa41bce6e24b35bd96bbd589f37384a892a5184d65","direction":"right"}]
  ✅ Verified

TX #3: "tx4: Eve -> Bob (7)"
  ➤ Leaf: 23b94486cf5fefdf91fc5dffc7ef5075c9a101befcd81b184b8e5c561e88c21d
  ➤ Proof: [{"hash":"22a8e34e52645f30786b6d25556a21d6f4bc7b0dff9ddfdcb7080ee6dd90f0a2","direction":"left"},{"hash":"379fa02fb915733c2969a6f292a09fb22cdcd0b6ef7377f2b621172584135767","direction":"left"},{"hash":"209ef6adbb1be15dddeae5fa41bce6e24b35bd96bbd589f37384a892a5184d65","direction":"right"}]
  ✅ Verified

TX #4: "tx5: Bob -> Alice (0.1)"
  ➤ Leaf: 35c640732c5ec8928d4da7d21b0d7525cb5eae6a057aeba18cc9079aa812f32c
  ➤ Proof: [{"hash":"35c640732c5ec8928d4da7d21b0d7525cb5eae6a057aeba18cc9079aa812f32c","direction":"right"},{"hash":"221f679c611962a9e310e6ffd2f9baa3cc3b93aa931c24ee0ef0f2e45a9d1734","direction":"right"},{"hash":"7e6a2b7604f68d3e48666b71cd88a47b9b65d9efb6996b588b842663bb6ef1db","direction":"left"}]
  ✅ Verified

✅ MerkleTree::selfTest() passed
```

## TestCryptoUtils.php

Tests the utils of converting tokens to and from WEI with basic math functions
Then runs a Fuzz Testing 50 times on random generated numbers.

```php
Expected Output:
=== Running CryptoUnits Tests ===
✅ PASS: toBaseUnit 1.5
✅ PASS: toBaseUnit 0.000000015
✅ PASS: toBaseUnit 0
✅ PASS: toBaseUnit invalid
✅ PASS: fromBaseUnit 1500000000000000000
✅ PASS: fromBaseUnit 0
✅ PASS: normalize base already
✅ PASS: normalize decimal
✅ PASS: isBaseUnit valid
✅ PASS: isBaseUnit invalid
✅ PASS: add base units
✅ PASS: subtract base units
✅ PASS: multiply 1.5 x 2
✅ PASS: divide 3e18 / 2e18 = 1.5
✅ PASS: divide by zero
✅ PASS: compare equal
✅ PASS: compare a > b
✅ PASS: compare a < b
=== Done ===

=== Fuzz Testing CryptoUnits (50 iterations) ===
Fuzz Test Result: ✅ 50 passed, ❌ 0 failed
```

## TestBlockCreation.php

Test creating of 3 blocks and adding then to the blockchain.

```php
Expected Output:

Currently working on this test.

```