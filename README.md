GenericHTMLFormProcessor
========================
Generic HTML Form Processor is a free program for facilitating data collection with HTML forms. It parses the input from any HTML form, then it automatically creates a MySQL database with one table in it (if not yet there) containing columns that are named according to the variables that were submitted through the HTML form. Thus, Generic HTML Form Processor relieves users from writing a script that parses form input and writes it into a database as well as from setting up a database.

You can use Generic HTML Form Processor for one-page studies, multiple-page studies, with input validation, random assignment of participants to experimental conditions, skip patterns, and password-protection. For details, see contents of folder examples.

The use of Generic HTML Form Processor is described in:

Göritz, A. S. & Birnbaum, M. H. (2005). Generic HTML Form Processor: A versatile PHP Script to save Web-collected data into a MySQL database. Behavior Research Methods, 37(4), 703-710.

The full text of the article can be [downloaded here](http://vg06.met.vgwort.de/na/688e9e771ec04733ae3fa2081fd193f0?l=http://www.goeritz.net/brmic/generic_HTML_processor.pdf).

For use on your own server just download the Generic HTML Form Processor (file generic.php).

Accompanying Material

To use the Generic HTML Form Processor on your own server, point the action attribute of your HTML form to where Generic HTML Form Processor resides:

<form method="post" action="path_to_generic_script/generic.php">

With multipage studies, paste one line of HTML code within the form tags into each study page except the last. This line defines the hidden variable GHFPvar_next_page and its value, which is the location of the next HTML page. An example is given below, in which sample2.htm is in the same directory as Generic HTML Form Processor:
<input type="hidden" name="GHFPvar_next_page" value="sample2.htm">

If the next study page were located on another server or not within the same directory as Generic HTML Form Processor, the value should be set to the absolute URL, for example:
<input type="hidden" name="GHFPvar_next_page" value="http://full_path_on_server.net/pagename.htm">

Please understand that there are the following limitations to using Generic HTML Form Processor: 
- We are not responsible for any data loss or malfunctioning of the script or database you or your respondents might experience. 
- We cannot give technical support for free.
- Generic HTML Form Processor is free software placed under GNU General Public Licence. 

Here is a short troubleshooting checklist in case the script does not run when you put it on your own server:
1. Make sure the Web server functions properly.
2. Make sure a current version of PHP (>=5.3.0) is correctly installed and configured.
3. Make sure a current version of MySQL is correctly installed and configured.
4. Make sure you have sufficient privileges in MySQL (described in more detail in the BRM article).

Example for a one-page survey can be found under examples/one_page/sample.htm.

Examples for a multi-page survey are located in examples/multi_page/: sample1.htm, sample2.htm, and sample3.htm.

Example for a multi-page survey with a JavaScript skip pattern for gender is located in examples/multi_page/skip_pattern/sample1a.htm

Example of a skip pattern with plain HTML is located in examples/multi_page/skip_pattern/gender.htm
