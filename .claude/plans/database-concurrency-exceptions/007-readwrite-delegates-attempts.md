# Task 007: ReadWriteConnection delegates attempts (merged into 002)

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Merged into task 002 by the devil's advocate review. `ReadWriteConnection` must take the new parameter in the same change that alters `TransactionInterface`, or every test that loads it fatals. Task 002 covers the forwarding code and its tests ("it delegates the attempts argument of transaction to the write connection", "it delegates one attempt by default"). Nothing to build here.

## Implementation Notes
Merged into 002.
