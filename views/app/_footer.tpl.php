        </div><!-- .container -->

        <footer>
            <a href='http://pedrogilcandeias.com/about'>@pgcandeias</a>
        </footer>

        <script>
            var timestamp = <?= microtime(true) * 10000 ?>,
                account = '<?= @$account->slug ?>'
            ;
        </script>
    </body>
</html>