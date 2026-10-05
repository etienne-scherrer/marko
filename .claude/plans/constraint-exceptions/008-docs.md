# Task 008: Docs page and README

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 007
**Retry count**: 0

## Description
Document the hierarchy, driver mapping and HTTP behaviour with a "handle a duplicate email" example.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md, database-pgsql.md, database-mysql.md, packages/database/README.md
- Follow docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs page lists the exception hierarchy and the SQLSTATE/driver-code mapping` (including MySQL 1364 and MariaDB 4025)
- [x] `docs page has a handle-a-duplicate-email example`
- [x] `docs page explains the 409 mapping and binding redaction`
- [x] `docs page explains what table() means` (the table that owns the constraint; for FK violations, the child table)
- [x] `docs page has an upgrade note`: `catch (PDOException)` around `query()`/`execute()`/repositories no longer matches. Catch `QueryException` and use `sqlState()` / `getPrevious()`, including for deadlock-retry loops
- [x] `docs page warns that PostgreSQL aborts the surrounding transaction after a violation`: a duplicate-email catch inside `transaction()` cannot keep using that transaction. Keep the example outside a transaction or roll back first

## Acceptance Criteria
- Docs match the shipped API

## Implementation Notes
