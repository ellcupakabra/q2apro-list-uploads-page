====================================
Question2Answer List Uploads v0.5
====================================
-----------
Description
-----------
This is a plugin for Question2Answer that displays all image uploads on a separate page including options to delete unused images

--------
Features
--------
- page only accessible by admin
- provides a page for showing image uploads of last 3 days, access-URL ``your-q2a-installation.com/listuploads``
- checks if each image is used within posts, pages or as avatar
- shows numbered list with: 
  * upload date/time, 
  * displayed image
  * blobid
  * information where image is used (question, answer, comment, avatar or custom page)
  * original filename
  * size of image in kB
  * user who uploaded
- listed images can be opened in lightbox (if installed_) or images are linked to themselves
- admin can delete single images or all images from database that are not used in posts
- admin can specifiy parameters by URL to filter images: /listuploads?days=30&remove=1&user=William35 (see instruction on listuploads page)

.. _installed: http://question2answer.org/qa/17523/implement-a-lightbox-effect-for-posted-images-q2a-tutorial

------------
Installation
------------
#. Install Question2Answer_
#. Get the source code for this plugin directly from github_
#. Extract the files.
#. Change language strings in file **qa-list-uploads-lang.php**
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

