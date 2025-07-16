# Test Suite Overview

Each file in `Tests/` exercises a core component of the ChainBase framework. Below you’ll find:

* **What** each test does
* **How** to run it
* **Key expected output**

---

## Prerequisites

1. Install dependencies via Composer:

   ```bash
   composer install
   ```

2. Ensure the PHP `bc` and `openssl` extensions are enabled.

3. From project root, tests live under `Tests/`. You can run any test directly with:

```bash
   php Tests/<TestFile>.php
```

---

## TestWallet.php

**What it does**

* Generates a new wallet.
* Regenerates from its mnemonic.
* Regenerates from its private key.
* Runs a hardcoded mnemonic sanity check.

**How to run**

```bash
  php Tests/TestWallet.php
```

**Key expected output**

```php
🔐 Wallet Creation Test
=========================
⚙️  Generating new test wallet...

📦 Wallet Details:
  ➤ Address     : 0xf9cfd35bec5210fcacf441385c83c304b4e1661b
  ➤ Public Key  : 0378d8ef4f36d9565f9ad2eecdf10a1576612015a60d4ecf91c3b89c8aebd70f31
  ➤ Private Key : 0x206936e40c8ab171383082433898a04c7186bc3b4e9fd8e7167605496b58c58a
  ➤ Mnemonic    : ramp cram addict race zebra cool gaze crash unhappy have middle begin

✅ Wallet successfully generated.

🔁 Regenerate from mnemonic...
… [same details] …

🧪 Regenerated Wallet From private key (Misses Mnemonic)... 
… [same details] …

🧠 Hardcoded mnemonic sanity check:
  ➤ Mnemonic    : label summer plug math hen cabin escape gadget decorate maximum crew enforce
  ➤ Address     : 0x2111dbd041e188db32d819d2e8f4ad6fba1279e5
  ➤ Public Key  : 0385084b7dcce5eb682e3ea417e251ba7c0492d460bf105f7cefaa258f1c65681a
  ➤ Private Key : 0xcf65d5d6e60e54dd86464f2c7e321d381e3d1042ee7c5aae079fb130ef044aac

✅ All tests passed.
```

---

## TestSignature.php

**What it does**

* Creates a test wallet.
* Signs two test messages (one with ASCII, one with a Cyrillic character).
* Verifies signature correctness and intolerance of non-ASCII tampering.
* Recovers the signing address.
* Runs a small fuzz-verification loop.

**How to run**

```bash
  php Tests/TestSignature.php
```

**Key expected output**

```
✒️ Signature Test Suite
========================

🔧 Generating test wallet…
… [wallet details array] …

✍️  Signing message…
… [signature components and hex] …

🔍 Verifying original message…
✔️  Signature valid? Yep ✅

🚨 Verifying tampered message (Cyrillic attack)…
✔️  Signature valid? Nay ❌

🔁 Recovering signer address…
🔍 Recovered address: 0xbc8af6d963dfd310ab3abe6a4c98d9fac459fc37
🔗 Matches wallet? Yes ✅

🧪 Running fuzzy verification tests…
📃 Fuzz test result: 50 passed ✅, 0 failed ❌
⏱️  Done in 6.48s
```

---

## TestLogger.php

**What it does**

* Writes one log entry at each level (error, warning, info, debug, success).
* Reads back the last 10 entries to verify persistence.

**How to run**

```bash
  php Tests/TestLogger.php
```

**Key expected output**

```
📝 ️ Logger Output Test
============================
🔧 Writing test logs…
[2025-07-15 22:22:29][error]   This is a test error log
[2025-07-15 22:22:29][warning] This is a test warning log
[2025-07-15 22:22:29][info]    This is a test info log
[2025-07-15 22:22:29][debug]   This is a test debug log
[2025-07-15 22:22:29][success] This is a test success log

✅ Log entries written. Check your logger output destination.

📂 Recent Log Output (tail -10):
… [last 10 lines, including the new ones] …
```

---

## TestMerkleTree.php

**What it does**

* Builds a Merkle tree from 5 fake transactions.
* Prints out the Merkle root.
* Verifies each leaf with its proof.
* Runs a built-in self-test.

**How to run**

```bash
  php Tests/TestMerkleTree.php
```

**Key expected output**

```
🌲 Merkle Tree Test Suite
=============================
🔧 Building Merkle Tree from 5 transactions...
🔗 Merkle Root: 0x7570dc1be58415c8deab1d823b671e78224e4299d9a398f66e0896393a446fab

🔍 Verifying TX #0: "tx1: Alice -> Bob (5)"
  ➤ Leaf Hash : 0x9fd2c67b016247e3da4074d367c2fa5e64b38e2896334462b0572843cdf389cb
  ➤ Proof     : [ … ]
  ✅ Valid Proof

… [same for TX #1–4] …

🔁 Self-Test Check…  
✅ MerkleTree::selfTest() passed

✅ All Merkle tests passed!
```

---

## TestCryptoUtils.php

**What it does**

* Exercises `CryptoUnits` conversions (decimal ↔ wei), arithmetic, comparisons, and invalid-input handling.
* Runs a 50-iteration fuzz test on random values to ensure robustness.

**How to run**

```bash
  php Tests/TestCryptoUtils.php
```

**Key expected output**

```
🔧 Running CryptoUnits Unit Tests
===============================
✅ PASS Convert 1.5 to wei
✅ PASS Convert 0.000000015 to wei
… [other unit tests] …
===============================

🔍 Fuzz Testing CryptoUnits (50 iterations)
==========================================
📃 Fuzz Results: 50 passed, 0 failed
==========================================
```

---

## TestBlockCreation.php

**What it does**

* Initializes an empty blockchain.
* Generates a batch of random signed transactions per block.
* Creates and appends 3 blocks, logging each addition.
* Prints a summary of each block’s header and verifies chain validity.

**How to run**

```bash
  php Tests/TestBlockCreation.php
```

**Key expected output**

```
⛓️  Creating new blockchain…
� Generating wallets…
➕ Generating blocks with random signed transactions…
[2025-07-16 10:06:05][info] ✅ Block #0x721aa… added.
[2025-07-16 10:06:06][info] ✅ Block #0x44e2fe… added.
[2025-07-16 10:06:07][info] ✅ Block #0x028429… added.

📃 Block Summary:
Block #0
  Hash      : 0x721aa45165af6…
  Prev Hash : 0
  TX Count  : 5
  Nonce     : 285
  Timestamp : 2025-07-16 10:06:05

… [Block #1 & #2] …

✅ Chain Valid? Yes
```

---

## TestTransaction.php

**What it does**

* **Single Test**: creates a transaction with a fixed payload, signs it, verifies it, and dumps the full transaction
  array (gas estimation, fee in wei & decimal, timestamp).
* **Fuzz Testing**: generates N random transactions (varying sender/recipient, amount, gasPrice, nonce, payload length),
  signs & verifies them, and reports success/failure counts.

**How to run**

```bash
  php Tests/TestTransaction.php
```

**Key expected output**

```
=== Single Test ===
Signed TX: 0x70312e1d07b95f47f5e2784cb3a421dc04599790f6dc44b863265727caea113334db7fb722cfff16deb828f1b738feeae0b0d8a9e7b9eda4548fb977026ec7461b
Valid? Yes ✅

Transaction Array:
Array
(
    [from]       => 0x134dc37decbfb46f5fd3ca2666c3697bc9bd3025
    [to]         => 0x702d926360d2cc58c587894d482bcf71a4d6b1e7
    [amount]     => 0.01
    [fee]        => 0.000214
    [nonce]      => 1
    [data]       => 0x7b227374…
    [gasLimit]   => 21400
    [gasPrice]   => 0.00000001
    [gasUsed]    => 21400
    [feeWei]     => 214000000000000
    [feeDecimal] => 0.000214
    [timestamp]  => 1752673928
)

=== Fuzz Testing ===

Fuzz test complete: 10 iterations
  ✅ Successes: 10
  ❌ Failures:  0
Success rate: 100%
```

You can adjust the number of fuzz iterations by editing the call to `fuzzTestTransactions(<count>)` in the script.

---

**That’s it!** Each test provides a clear pass/fail signal and exercises critical components—wallets, signatures,
logging, Merkle trees, token-unit math, block creation, and transaction handling—for confidence in your Layer-2
framework.
