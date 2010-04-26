# PHP Library

More info coming soon

## Includes

In all files, including files is not done with include_once(), but rather include() for
performance reasons.

To check that a file is not included twice, I check for the class name.

After doing some testing I realized that this still is much faster then using include_once().