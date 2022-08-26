import pymysql
import pymysql.cursors
import os
import sys


def connect():
    """ Perform a new database connection
    :return:
    """
    try:
        return pymysql.connect(host=os.getenv('DB_HOST'), user=os.getenv('DB_USERNAME'),
                               passwd=os.getenv('DB_PASSWORD'), db=os.getenv('DB_DATABASE'), connect_timeout=10,
                               cursorclass=pymysql.cursors.DictCursor)
    except pymysql.MySQLError as e:
        print(repr(e))
        sys.exit()
