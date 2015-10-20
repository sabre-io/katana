ChangeLog
=========

0.4.0 (2015-10-20)
------------------

* Updated all dependencies to their latest versions.
* Removed a lot of unneeded code (removed `katana://` protocol code, custom
  exception handlers).
* Version information is now in `Sabre\Katana\Version` class.
* Moved `vendor` directory to root.
* Removed `Sabre\Katana\Database` (a plain PDO object is now used).
* This version bundles sabre/dav version 3.0.5


0.3.2 (2015-05-12)
------------------

* #276: Remove home collections, not the `home/` directory (@Hywan).
* #275: Ensure a better compatibility across Linux distributions (@Hywan).
* This version bundles sabre/dav version 3.0.1


0.3.1 (2015-05-12)
------------------

* #272: Prevent query parameters to be corrupted (@Hywan).
* #270: Fix bug 319607 of Debian with `find` (@Hywan).
* #269: Fix a conflict between `data/home/` and `.gitignore` (@Hywan).
* #265: Fix path to `server.json` in the Settings panel (@Hywan).


0.3.0 (2015-05-09)
------------------

* #259: Print an error message if prior versions of IE9 (@Hywan).
* #258: Better database error feedbacks while installing (@Hywan).
* #257: No longer redirect when trying to re-install (@Hywan).
* #256: New architecture: `data/` is the only writable directory (@Hywan).
* #254: Use `sabre/dav` 3.0 (@Hywan).
* #249: Add the Settings panel (@Hywan).
* #248: Better compatibility with PHP7 (@Hywan).
* #244: Add CalDAV scheduling support (iMIP) (@Hywan).
* #243: Add CalDAV scheduling support (iTIP) (@Hywan).
* #242: Serve files with appropriated MIME type (@Hywan).
* #239: Each principal has a `public/` directory in their home (@Hywan).
* #233: Collection synchronization (@Hywan).


0.2.0 (2015-05-18)
------------------

* #226: Remove Apache and nginx configuration templates (@Hywan).
* #221: Passwords schema updated for MySQL (@Hywan).
* #216: Better password and email error detection when installing (@Hywan).
* #213: New `system` plugin (@Hywan).
* #211: WebDAV support (@Hywan).
* #208: Show instructions for DAV clients (@Hywan).
* #205: Address books can be created, edited, deleted and exported (@Hywan).
* #198: Task lists can be created, edited, deleted and exported (@Hywan).
* #197: Fix `node.children` in the WebDAV adapter on Safari (@Hywan).
* #195: Calendars can be edited (@Hywan).
* #194: Calendars can be deleted (@Hywan).
* #186: Calendars can be created (@Hywan).
* #185: Calendars can be exported (@Hywan).
* #182: When password is empty, no error is shown when creating a new user (@Hywan).
* #184: List calendars of a user (in addition to a new interface) (@Hywan).
* #180: Prompt the URL to the administration interface in the CLI installation (@Hywan).
* #176: Reduce the archive weight (@Hywan).
* #175: Automate build with a `Makefile` (@Hywan).


0.1.1 (2015-04-27)
------------------

* #168: Only the administrator can login (@Hywan).
* #157: Making sure Travis does not fail (@evert, @DominikTo, @Hywan).
* #163: Fix the `vendor/` directory on Windows with a VM (@Hywan).
* #162: Fix installation page's layout on Webkit (@Hywan).


0.1.0 (2015-04-23)
------------------

* First version!
