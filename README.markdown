# PHP Library

More info coming soon


## Short introduction

This library is meant to facilitate common tasks in PHP.


### Dao

The biggest and probably most used part of the library is the Dao.

Once your Dao is configured (simply define the types of columns you have in your database table), you access your database like this:

    <?php
    $user = $userDao->getById(4);
    $user->set('firstName', 'John');
    $user->lastName = 'Doe'; // Is the same as $user->set('lastName', 'Doe');
    $user->save();
    ?>

You can also chain calls like this:

    <?php
    $userDao->get() // Gets a "raw" object, ready to be filled with data.
      ->set('firstName', 'John')
      ->set('lastName', 'Doe')
      ->save(); // Inserts the object in the database.
    ?>

Daos support import/export definitions (so you can rename table columns the way you like them in your php script), escape all values correctly, and check for the correct values when setting them.

You can specify if a column is allowed to be null, and the type for it.
Supported types are:

- INT
- FLOAT
- STRING
- TIMESTAMP
- BOOL
- ENUM (You can specify the values allowed in an enum so php will check for it)


There are currently 4 Dao Implementations

- MysqlDao (extends the SqlDao)
- PostgresqlDao (extends the SqlDao)
- JsonDao (extends the FileSourceDao. This Dao is used to get files from some DataSource, and decode the content with Json)
- XmlDao (extends the FileSourceDao. The same as the JsonDao but for Xml)

But it's very easy to add implementations.


## Testing

This library is well tested.

It contains over 40 test cases and over 200 tests.

I'm using [snaptest](http://github.com/Jakobo/snaptest) for testing.


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