# WAWAS - The WebAbility(r) PHP Web and Application Server v1.0.7


[![Build Status](https://travis-ci.org/webability/WAWAS.svg?branch=master)](https://travis-ci.org/webability/WAWAS)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/webability/WAWAS.svg)](http://isitmaintained.com/project/webability/WAWAS "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/webability/WAWAS.svg)](http://isitmaintained.com/project/webability/WAWAS "Percentage of issues still open")
[![Join the chat at https://gitter.im/webability](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/webability/WAWAS?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


This is a real multithreaded application server 100% coded in PHP 7.

It uses the pthreads PECL extension, in CLI mode.

It integrates the Xamboo CMS Framework and is build on top of the DomCore PHP Fundation Classes.

---

## Installation:

### Download and compile PHP7 in CLI mode:
In this example it is compiled and installed into an alternative directory.
It may be the default executable PHP in your OS too.

```bash
./configure --prefix=/usr/local/php7 --exec-prefix=/usr/local/php7 \
   --enable-sockets --enable-pcntl --enable-maintainer-zts \
   --enable-shmop --enable-sysvsem --enable-sysvshm
   # you may add anything you need, for example:
   # --with-pgsql=/usr/pgsql-9.2/ --with-mysqli --with-xmlrpc --with-curl

make
make install
ln -s /usr/local/php7/bin/php /usr/local/bin/php7
/usr/local/php7/bin/pecl install pthreads
```

### Download wawas and install it into a local directory

```bash
cd /home/sites
wget http://webability.info/download/wawas/wawas.tar.gz
tar zxvf wawas.tar.gz
cd wawas
```

### Edit configuration files (IPs to listen to, vhosts, etc)

```bash
vi conf/server.conf
vi conf/http11/vhosts.conf
```

### Go to the main installation directory (where runner.php is placed)

```bash
cd /home/sites/wawas
```

### Run the server:

```bash
php7 runner.php
```

### Or run the server as a daemon:

Configure in server.conf <daemon><state>1</state>...
to run in daemon mode, or the wawasctl will wait for any CTRL-C to finish

```bash
./wawasctl start
```

---

## TO DO at a glance:

### Server:
- Hooks on modules (working on them)
- implement maxs on #threads, #clients, #hits, sizes, headers etc
- heap corrupted when an error occurs (no vhost, 501, etc)

### Protocol HTTP2:
Analize, read, implement ?

### Protocol HTTP11:
- Authentication
- Chunks
- SSL/HTTPS in house
- Hooks
- gzip/compressed
- etags
- portable vhost names (full named host) to detect variants on resolution
- check anything against RFC to be 100% compliant
- Support big files for sending and streaming (and not saturate the memory) (see mod_static, eventually mod_streaming ?)

### Modules:
- mod_static => check mimes types and errors
- mod_file => DO ! view files in a directory (FTP type)
- mod_admin => generate the full admin site, hooks, realtile stats/JSON (Comet?) based on Xamboo
- mod_log => log on vhosts and others
- mod_php => use sandbox ? capture output, errors control, namespaces, overload classes ?
- mod_xamboo => integrates SERVER-APP variables (ej. REMOTE_HOST, REMOTE_ADDR, etc)
- mod_redirect => DO ! rediret engine
- mod_session => DO ! manage sessions

### Other interesting modules:
- Implement PSP (PHP Server Pages)
- servlets
- netbeans ?
- Comet module
- WSDL module
- Direct Socket

### Examples
- a game, twit app, prives polling, realtime graph, edit document, etc
- terminate the chat

### Admin
- Implement admin console fully realtime

---

## Version Changes Control

### Build 7 2016/??
- Implementation of phpunit tests for travis

### Build 6 2016/01/05
- CTRL-C Gracefully ends the application, it does not hang anymore
- shutdown function has been removed

### Build 5 2016/01/05
- tls parameter added in the listener definition
- Socket protocol added
- Comet Module added
- TLS on stream working, SSL supported
- TLSEngine created and implemented into the client, still working on structures
- ThreadedServer created for blocking streams on extra thread
- Listener is now volatile for ThreadedServer

### Build 4 2015/12/10
- wawasctl included to start as a daemon
- buffer output catch on protocol to send notice/warning/errors to client
- ModuleAdmin is now a subset of Xamboo application server
- /include/admin added for administration application
- siteadmin added for administration root directory, JS and CSS added
- HTTP11 protocol timeouts and max petitions implemented
- Added context->petitions to know number of petitions of the thread, available on admin too
- listener bufferlenght implemented
- enhancement of SHM, mutex, semaphore and concurrency to make sure SHM is not corrupted under heavy load
- Added config parameter keepalive in vhost configuration
- Enhanced sockets control to ignore warnings on unexpected disconnection
- Error control on HTTP11Protocol->process has been improved for bad formed headers
- Definition of configuration files have been moved to include/configdef so it's easy to upgrade without moving the actual configuration
- Sockets timeouts adjusted so the execution is extremely light on CPU load 
- Added box load into the admin system

### Build 3 2015/12/02
- The engine has been totally rewritten for PHP7, namespaces, pthreads v3, and is now really multithreaded.
- The Xamboo has been integrated with the server as a module. This is the application server.
- The server is stable and can server static files and applications, however it is far to be fully implemented (protocols, modules, etc)


### Build 0002 2012/02/01
- wawasctl adjusted to work with wawas.php
- creation of WAWASBase.lib
- HTTP11: Implementation of LWS in headers parser (RFC 2068 p15, headers can be multilines)
- HTTP11: Multiple same keyword headers are now set into an array
- HTTP11: Implementation of anchor into request parser
- ModuleBox: Integration of Box pattern and module

### Build 0001 2012/02/01
This is the first alpha version.
WAWAS is not still fully funcional and is under heavy developement.
This is an alpha version, and may suffer any type of modifications anytime.
This version "works" more or less, and may have lots of bugs.
This is not a stable version or an official release.
Do not use it for production sites.

---

## Basic Manual

---

## Configuration Reference

---

## Protocols Reference

### HTTP11

### Socket

---

## Modules manual

### ModuleAdmin

### ModuleLog

### ModulePHP

### ModuleStatic

### ModuleXamboo

---

## DomCore Manual

[Manual](http://www.webability.info/?P=documentacion&wiki=/DomCore)

---

## Xamboo Manual

[Manual](http://www.webability.info/?P=documentacion&wiki=/Xamboo)
