Stakhanovist Queue Test Suite
-----------------------------

### Advices

An advice to those who are writing test cases.

Be sure to **receive()** for every **send()** otherwise previous failed tests will interfer with future tests.

If necessary you may have to delete the queue before creating it.