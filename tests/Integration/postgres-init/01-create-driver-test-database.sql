-- The database-pgsql driver integration tests (MARKO_TEST_PGSQL_DATABASE)
-- get their own database: the fixture suite drops marko_integration before
-- every case.
CREATE DATABASE marko_test;
