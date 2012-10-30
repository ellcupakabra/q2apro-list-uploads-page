====================================
Question2Answer List Uploads v0.2
====================================
-----------
Description
-----------
This is a plugin for **Question2Answer** that displays the newest uploads (images) of the last days on a separate page

--------
Features
--------
- page only accessible by admin
- provides a page for showing newest image uploads of last x days, access-URL ``your-q2a-installation.com/listuploads``
- shows upload date, blobid, size of image in kB, and user that uploaded
- admin can specifiy number of past days to show images by URL: listuploads?days=5 
- checks if each image is used within posts or as avatar
- admin can delete images that are not used

------------
Installation
------------
#. Install Question2Answer_
#. Get the source code for this plugin directly from github_
#. Extract the files.
#. Change language strings in file **qa-list-uploads-lang.php**
#. Optional: Change settings in file qa-list-uploads-page.php
#. Upload the files to a subfolder called ``q2a-list-uploads-page`` inside the ``qa-plugins`` folder of your Q2A installation.
#. Navigate to your site, go to **Admin -> Plugins** on your q2a install. Check if plugin "List Uploads Page" is listed.
#. Navigate to yourq2asite.com/listuploads to see the new uploads listed

.. _Question2Answer: http://www.question2answer.org/install.php
.. _github: https://github.com/echteinfachtv/q2a-list-uploads-page

----------
Disclaimer
----------
This is **beta** code. It is probably okay for production environments, but may not work exactly as expected. You bear the risk. Refunds will not be given!

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

-------
Copyright
-------
All code herein is OpenSource_. Feel free to build upon it and share with the world.

.. _OpenSource: http://www.gnu.org/licenses/gpl.html

---------
About q2a
---------
Question2Answer is a free and open source platform for Q&A sites. For more information, visit: www.question2answer.org

---------
Final Note
---------
If you use the plugin:
+ Consider joining the Question2Answer-Forum_, answer some questions or write your own plugin!
+ You can use the code of this plugin to learn more about q2a-plugins. It is commented code.
+ Thanks!

.. _Question2Answer-Forum: http://www.question2answer.org/qa/

