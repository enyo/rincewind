# PHP Library

More info coming soon

## Includes

In all files, including files is not done with include_once(), but rather include() for
performance reasons.

To check that a file is not included twice, I check for the class name.

After doing some testing I realized that this still is much faster then using include_once().


## FAQ

### Daos

**Q** *Why does get() throw an exception instead of simply returning null when the object is not found?*  
**A** Because I find it important that you can chain commands without having to worry if the object has been found.  
A simple example:

`$myDao->getById(1)->set('name', 'Matthias')->save();`

This would not be possible if getById() returned null.