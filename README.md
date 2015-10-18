# Moove

A proxy server for puush file sharing service

This is my rendition of a puush server for my own use and education.  Reverse engineered with WireShark.  Only works with Windows afaik.

# Setup

### Server
1. Clone to web server root.
2. Add create a new virtual host and set it's server alias to `puush.me`
3. Visit `setup.php` to quickly setup everything
4. Delete `setup.php` so people can't exploit it

### Client
1. Logout of Puush client
2. Close Puush client
3. Open up `%appdata%\puush\puush.ini`
4. Add your server IP to ProxyServer line (i.e. `ProxyServer = example.com`)
5. Add `ProxyServer = 80` to the next line
6. Reopen Puush Client
7. Login to Puush client with new credentials


# Notes
* Requires PDO SQLITE module for data persistence
* Requires MCRYPT module for file encryption
* Users register at http://example.com/register
* First user is the administrator
* Administrator generates invite codes by sign "reregistering" with invite code as "generate"


# License

Under the [WTFPL](http://www.wtfpl.net/)
