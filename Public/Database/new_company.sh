#!/bin/sh

/alidata/server/mysql/bin/mysql -h10.129.13.14 -uroot -p0274b2bb54 teamin < /data/htdocs/teamin/Public/Database/init_base.sql
/alidata/server/mysql/bin/mysql -h10.129.13.14 -uroot -p0274b2bb54 $1 < /data/htdocs/teamin/Public/Database/init_data.sql
/alidata/server/mysql/bin/mysql -h10.129.13.14 -uroot -p0274b2bb54 $1 < /data/htdocs/teamin/Public/Database/init_table.sql
