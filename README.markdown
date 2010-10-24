# Rincewind

Is a PHP library.


## Short introduction

This library is meant to facilitate common tasks in PHP.


### Dao

The biggest and probably most used part of the library is the Dao.

Once your Dao is configured (simply define the attributes you have in your resource), you access your datasource like this:

    <?php
    $user = $userDao->getById($id);
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

One thing that's really cool with Daos are references.

#### References

Eg.: You can specify that the attribute `address_id` points to the foreign key `id` on the AddressDao, and that you can access that object on the `address` property.
This means that you can then simply access the address like this:

    <?php
    $user = $userDao->getById($id);
    $address = $user->address; // Instead of: $address = $addressDao->getById($user->addressId)
    ?>

This also works with toMany references, and the iterator only fetches the data when accessed.

Json:

    { "id": 4, "username": "Joe", "address_ids": [ 3, 6, 8, 9 ] }}

Your application:

    <?php
    $user = $userDao->getById($id);
    foreach ($user->addresses as $address) {
      // Your code here
    }
    ?>

Even better: for data sources that support it (eg: Json) you can specify the referenced data directly, so it doesn't have to be fetched, and create the unwanted overhead of a new request.
Those two possibilities can be mixed without any problem, and it's the servers choice then to submit the data directly, or just give the id.

The submitted data could look like this:

    { "id": 4, "username": "Joe", "address_id": 3, "address": { "id": 3, "street": "Somethinglane 3" }}

This works for chained references too of course!
This would be the most direct way to access the country name of the user with username 'Billy' if you had 4 resources: users, addresses, cities and countries, and they were all joined:

    <?php
    $countryName = $userDao->get()->set('username', 'Billy')
      ->load() // Actually loads the entry from the database. (Throws an exception if it's not found)
      ->address // Makes the join between users.address_id and the addresses.id
      ->city // Makes the join between addresses.city_id and cities.id
      ->country // Makes the join between cities.country_id and countries.id
      ->name; // Reads the name attribute of country.
    ?>

Well, and probably the coolest thing is the possibility to define references, and not even define the id. This is especially cool when you are working with a FileDataSource that submits JSON from a Java backend for example.
In this case the server really can decide if it transmits the id, or the dataset. You'll never see anything of it in your application.
The two JSON objects would then result in exactly the same:

    { "id": 4, "address": { "id": 3, "street": "Somethinglane 3" }}
    { "id": 4, "address": 3 }

When you access the reference (with `$user->address`), the reference checks the present data, and either returns the record directly (if the data is set), or fetches the record with the id.

#### Import/Export definitions

Daos support import/export definitions (so you can rename datasource attributes the way you like them in your php script), escape all values correctly, and check for the correct values when setting them.

You can specify if an attribute is allowed to be null, and the type for it.
Supported types are:

- INT
- FLOAT
- STRING
- TIMESTAMP
- BOOL
- ENUM (You can specify the values allowed in an enum so php will check for it)
- SEQUENCE (Only used for ID lists)


#### Different implementations

There are currently 4 Dao Implementations

- MysqlDao (extends the SqlDao)
- PostgresqlDao (extends the SqlDao)
- JsonDao (extends the FileSourceDao. This Dao is used to get files from some DataSource, and decode the content with Json)
- XmlDao (extends the FileSourceDao. The same as the JsonDao but for Xml)

But it's very easy to add implementations.


## Testing

This library is well tested.

It contains over 50 test cases and over 300 tests.

I'm currently in between testing frameworks. I used [snaptest](http://github.com/Jakobo/snaptest), but I'm currently migrating to phpunit. So at this moment lots of old snaptest tests don't work right now.


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
If you don't need chaining, but simply want to test if a row exists, use find() instead, which does the same as get, but returns null if nothing's found.