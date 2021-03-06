# Public Whip [![Stories in Ready](https://badge.waffle.io/openaustralia/publicwhip.png?label=ready)](https://waffle.io/openaustralia/publicwhip) [![Code Climate](https://codeclimate.com/github/openaustralia/publicwhip.png)](https://codeclimate.com/github/openaustralia/publicwhip)

This is an Australian fork of the UK website [Public Whip](http://www.publicwhip.org.uk/).

The easiest way to get a development environment set up is to use [vagrant][1]
and [virtualbox][2] to bring up a virtual machine. Once you've got them
installed and have the publicwhip source code, `cd` into the source code
directory and run `vagrant up`. This will download the base virtualbox image
and set up the development environment, be prepared for a bit of a wait.

Once that's done, you'll find the original PHP app available at localhost:8080
(vagrant will automatically forward the port from the VM to the host). Run the
rspec tests from inside the VM like this:

* `vagrant ssh`
* `cd /vagrant`
* `bundle exec rake PHP_SERVER=localhost`

Assuming they pass, you can start the rails server:

* `bundle exec rails server`

Once it is up you can browse to localhost:3000 on the host.

When manually testing the site, the "sign up" confirmation emails will
automatically go to a dummy smtp server called [mailcatcher][3]. To check the
emails, browse to localhost:1080 on the host.

If vagrant reports that it can't mount the `/vagrant` virtualbox shared folder,
it's becuase the VM has had it's kernel updated. Run
`vagrant provision && vagrant reload` and you should be back in business.

[1]: http://www.vagrantup.com/
[2]: https://www.virtualbox.org/
[3]: http://mailcatcher.me/

## Development setup

If you're not using Vagrant, this is what you need to do to set up the Rails applicaiton.

Copy `config/database.yml.example` to `config/database.yml` and fill in the appropriate details. Your username and password for the test and development database must match for tests to work.

Copy `config/secrets.yml.example` to `config/secrets.yml` and run `bundle exec rake secret` to generate a secret_key_base for your environments.

Ensure `$hidden_hash_var` in your `config.php` is set to an empty string so that logged in page tests work.

    # Install bundle
    bundle install

    # Run tests (PHP_SERVER is the address of the local PHP version of the app)
    bundle exec rake PHP_SERVER=localhost

    # Start the server
    bundle exec rails server
