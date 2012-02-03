@UTF-8

WAWAS - The WebAbility(r) Web and Application Server v1

This is the build 0001
----------------------

Changing a build:
- Edit wawas.php and change version number on line 25.

--------------------------------------------------------------------------------------

Version Changes Control
=============================

Build 0001 2012/02/01
=============================
This is the first alpha version.
WAWAS is not still fully funcional and is under heavy developement.
This is an alpha version, and may suffer any type of modifications anytime.
This version "works" more or less, and may have lots of bugs.
This is not a stable version or an official release.
Do not use it for production sites.











TO DO LIST
=============================
(that are just generic notes on wawas)

GENERAL

4 modes to use:
HTTP - normal web server / http protocol
WAS - application Server / http protocol
COMET - comet server / http protocol
SERVER - session server / any protocol with library to manage

COMET:
4 methods to use:
- PERSONAL: me vs me on messages (control messages, etc)
- TO A SID (private message with another SID)
- TO A GROUP (like an IRC channel)
- TO GLOBAL (to anybody)
SID, GROUP and GLOBAL can be filtered



HTTP
- Implement mime types
- Implement CGI
- Implement APP server
- Implement PSP (PHP Server Pages)
- Poll messages by virtual host
- Link uid user vs ip and unique session for next ajax call, do not change ID
- check disconnect real vs disconnect for changing ajax thread
- Implement channels independant and declare channels into config
- send to a channel /CHANNEL/
- send to global /COMET/
- send to a group /GROUP/
- send to a client specific /SID/



Hacer solamente 1 ID aleatorio del cliente + un serial 1, 2, 3... cada reconnect
Hacer un param de config que tanto se tiene que estar cerca el IP de un cliente
x IP, por clase C, B, A de parecido para un NAT-. por defecto: C
el servidor envia la orden restart al cliente para que el cliente se reconecte
un parametro del servidor timeout 1 minuto por defecto.
hacer un timeframe autoajustable. 20 por segundo. si el tiempo de calculo aumenta, disminuye el tiempo de espera del socket no bloqueante
$clients y $sids por domains
domains autorizados dentro de la config
sigterm => envia disconnect a todos los clientes ademas de cerrar los , antes de cerrarlos.
esperar que se cierran por si solos. (hace un gracefull stop)


Classes / forked instances hierarchy:

<WAWAS> launch <Server>
  <Server> contains all the <Listener>
  <Server> create the <Client> when there is a connection
    <Client> temporarly cache data from client while we decide which protocol
    <Client> calls <Protocol> to determine which one is OK
      <Protocol> builds Request while it gets in, call partial module that build <Response>
      <Client> follow sending input data to <Protocol> and send back any output data from <Response>

Global server config, main classes, etc
- Server con Listeners based on IP/Port
  - Workers
    - Hit separated
- Global classes, accesed by RPC (Remote Procedure Call) on PIPE/otro metodo
- Domain global classes, by RPC, include Session class, Domain class

PSP: PHP server pages

<? xxx ?>
html
<? xxx ?>
html
@var

BOXES: box patterns

REALMS

SESSIONS

SERVLETS

BEANS, SCOPE

-----------------------------------------------------------------
Diagram:
Launcher load preconfig (protocols and modules)
         load protocols and insert specific config
         load modules and insert specific config
         load full config
         launch Server

Server creates listeners
       open listeners
       loop to listen on each listener & clients
         When listener get a new hit: Create client
         When client get info: send to protocol

Client get info, check which protocol match
                 send to protocol when found

2 ways: FORK, THREAD
The protocol decides which one will be used (if forked is available too)



Libraries
- Implement Modules

Javascript
- finish to Implement the library for IE (timeouts, re-call)
- Make a circuit for the error on open channel sent by the server (201)

Examples
- a game, twit app, prives polling, realtime graph, edit document, etc
- terminate the chat

Admin
- Implement admin console

