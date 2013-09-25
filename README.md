Replayed
========

_Based off [Unplayed](http://shauninman.com/archive/2011/04/18/unplayed) by Shaun Inman_

A tool for maintaining lists of games, which could probably be reused for lists of books, movies, etc.


Changes from Unplayed
---------------------

This version aims to be 100% compatible with Unplayed lists, with additional functionality:

- Tagging is supported using hashtags, which adds classes to the items for styling.
- Classes for platforms are added to items for styling.
- Titles link to the best-guess site for the game.


Installation
------------

Copy the files to your directory. Edit the Markdown files in the `games/` directory. [Browse the available cartridges](http://github.com/adamaveray/replayed-cartridges) to change the appearance, or write your own!

_Bonus: Sync the `games/` directory with Dropbox on your server for extra magic._


Editing
-------

Lists are written in Markdown. The format for each entry should be the following:

~~~md
- Title of Game (Platform) (Any notes to keep with the game) #a-tag #another-tag
~~~

Only the title is necessary – the platform, notes and tags are optional.

The default lists are only examples – any number of lists can be created, which will be displayed alphabetically. Prefixing each list Markdown source file with a number (e.g. `1. Unplayed.md` or `1-unplayed.md` vs `Unplayed.md`) will let you control the ordering.


Styling
-------

The system can be re-themed easily by writing "cartridges" – a fancy name for a new stylesheet and any images the theme uses. You can also [browse the pre-made cartridges](http://github.com/adamaveray/replayed-cartridges).
