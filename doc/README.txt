How to use the WAWAS:

1. Download, compile and install PHP version 5.0.4

You must compile PHP as CLI, with socket support

IMPORTANT: YOU NEED THE FOLLOWING OPTIONS WHILE COMPILING PHP5:
  --enable-socket     on Windows and Unix*s
  --enable-pcntl      on Unix*s if you plan to use multithread server

2. Run the server:

if php5 is your CLI executable:

# php5 wawas.php {options}

options are:

--multithread -m
denotes mutithread server and is optional

--daemon -d
denotes a daemon mode, and is optional
IMPORTANT: If you run wawas in daemon mode, you MUST redirect outputs to a file or to /dev/null
If you don't, wawas (>> php) will crash and zombie where you logout

# php5 wawas.php -d >/var/log/wawas.log

--version -v
ask for the version

--debug
enters into debug mode: many information about parsing, sockets and clients
is displayed onto screen

--help -h -?
print help then exits

--configtest -ct
Make a test to know if the config file is good then exits
            
--quiet -q
will write absolutly nothing on the screen



-----------------------------------------------------
- Config file
-----------------------------------------------------

The configuration file is an XML file.
The file has 5 sections:

Please Check the example file in conf/wawas.conf

<server>

<connections>

<listeners>

<loggers>

<virtualhosts>

