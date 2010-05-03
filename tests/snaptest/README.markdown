README - SnapTest
=================

Core Branch: [http://github.com/Jakobo/snaptest/tree/master](http://github.com/Jakobo/snaptest/tree/master)

Issues: [http://github.com/Jakobo/snaptest/issues](http://github.com/Jakobo/snaptest/issues)

Wiki: [http://wiki.github.com/Jakobo/snaptest/](http://wiki.github.com/Jakobo/snaptest/)

Introduction
------------
SnapTest is a powerful unit testing framework for PHP 5+, leveraging PHP's unique runtime language to simplify the unit test process without sacrificing the agility tests provide.

SnapTest is a free software project licensed under the new BSD License.

Getting Started
---------------

Place Snap wherever you want, and run a self test:

   1. if you have php in an obvious location (path, /usr/bin, /usr/local/bin, /opt/local/bin), run the command ./snaptest.sh ./ from inside the snaptest directory.
   2. if php is not in an obvious location, you can run ./snaptest.sh ./ --php<path> where <path> indicates the location of your php binary.
   3. if shell scripting for whatever reason isn't working, you can also use the PHP binary directly by calling <php> snaptest.php --path=<php> ./ where <php> is the location of your php binary. 

When ran, you should see output like the following:

<code><pre>
user@host> ./snaptest.sh ./
..................................................................................................................................
______________________________________________________________________
Total Cases:    53
Total Tests:    130
Total Pass:     130
Total Defects:  0
Total Failures: 0
Total Skips:    0
Total Todo:     0
</pre></code>

Sanity Check: There are currently 53 Cases and 130 Tests

If you don't get any failures (marked with an F followed by information about the error, you're ready to go!

From here, check out [http://wiki.github.com/Jakobo/snaptest/base-unit-test-class](http://wiki.github.com/Jakobo/snaptest/base-unit-test-class) Step 2 to start writing your own tests.

License
-------
  * SnapTest >= 1.2.0 is licensed under the new BSD License (please see LICENSE for full terms)