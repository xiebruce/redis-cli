Redis Cli is a web redis command-line interface like redis-cli!

The default login account:

Username: redis
Password: password

You must change the account when use in production server!

Added flag parameter:

get <key> [flag]
When specified the flag parameter, you can get a binary form string.
Example:
redis> setbit foo 0 1
(integer) 1
redis> get foo 1
"10000000"

keys <pattern> [flag]
When specified the flag parameter, you can get a list of the result keys.
Example:
redis> set foo1 1
OK
redis> set foo2 2
OK
redis> set foo3 3
OK
redis> keys foo* 1
1."foo1"
2."foo2"
3."foo3"
"foo1" "foo2" "foo3"
