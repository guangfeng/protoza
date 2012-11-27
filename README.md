protoza
=======

a simple mapreduce on php5


useage
======
* -v --version: show version for PHP MapReduce
* -w --work --workdir: spec the working dir
* -c --childs :tell me how many child would fork
* -f --file: spec what the PMR work on?
* --task: spec the file contains map and reduce function.

example
=======
<code>
$ phpmr -w dumps/ -c 2 -f pop.log --task worker.php
</code>


