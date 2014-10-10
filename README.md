# Moove

A proxy server for puush file sharing service

This is my rendition of a puush server for my own use and education.  Reverse engineered with WireShark.  Only works with Windows afaik.

# Setup

### Server
1. Clone to web server root.
2. Add create a new virtual host and add `puush.me` as a server alias

### Client
1. Logout of Puush client
2. Close Puush client
3. Open up `%appdata%\puush\puush.ini`
4. Add your server IP to ProxyServer line (i.e. `ProxyServer = mv.ssttevee.com`)
5. Add `ProxyServer = 80` to the next line
6. Reopen Puush Client
7. Login to Puush client with new credentials


# Notes
* Not sure if you need SSL, but I couldn't get it to work without it
* Uses PDO SQLITE
* Requires php-mcrypt for file encryption
* Users register at http://example.com/register
* First user acts as administrator
* Administrator generates invite codes by sign "reregistering" with invite code as "generate"


# License

Under the [WTFPL](http://www.wtfpl.net/)
