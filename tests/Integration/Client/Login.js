casper.test.begin(
    'Login',
    function(test) {

        casper.start(
            'http://127.0.0.1:57005/admin.php',
            function() {
                test.assertTitle('Administration of sabre/katana');
            }
        );

        casper.run(
            function() {
                test.done();
            }
        );

    }
);
