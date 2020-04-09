# simple-site

This is a very simple set of processes for creating a standard set of webpages based on a simple set of json files. This project is intended to work along side other projects to provide a simple way of creating a set of consistent webpages, which can be delivered as part of your own GitHub project using [GitHub pages](https://pages.github.com/). 

An example set of pages, including more detailed instructions and examples can be seen at: [simple-site](https://jpadfield.github.io/simple-site/)

The content of the pages are controlled with three [json](https://en.wikipedia.org/wiki/JSON) files stored in the build folder, further details are supplied within the build directory [README](./build/README.md) file:

* Various site wide details are defined within the [site.json](./build/site.json) file.
* The content for the various main pages, which will be listed as tabs, are defined within the [pages.json](./build/pages.json) file.
* Any required sub-pages can then also be defined within the [sub-pages.json](./build/sub-pages.json) file. These sub-pages, or even sub-sub-pages etc, will not be given main tabs, but will be listed within drop-down menus from the related main page tab.

# Screenshots 
<img src="./docs/graphics/example screenshot 01.png" width="50%" alt="Example Screenshot"><img src="./docs/graphics/example screenshot 02.png" width="50%" alt="Example Screenshot">

Once any of the json files are updated, within the GitHub repository, a new set of html pages are automatically generated, by GitHub, using the [build.php](build/build/php) file. Please note that although the new html pages are generated quite quickly it may take a few minutes before the changes are pushed through to your actual GitHub web pages.

# Installation

To make use of this system it is recommended that you:
* [Join GitHub](https://github.com/join)
and then either:
* [Copy/Fork](https://help.github.com/en/github/getting-started-with-github/fork-a-repo) the repository, edit the sample json files and begin to build your own project.
* [Download/Clone](https://help.github.com/en/github/creating-cloning-and-archiving-repositories/cloning-a-repository) a copy of the repository to your local machine, edit the json files as required and then just copy the required folders and files into you own existing repository.

For the full system to work, including the automatic rebuild process you will need to ensure your own repository includes:
* A copy of the **build** folder.
* A copy of the **docs** folder.
* A copy of the [build.yml](.github/workflows/build.yml) setup as a GitHub action.
* A copy of any required licence files.

The automatic process of building the pages is controlled by a **GitHub Action**, there is quite a lot of documentation available about setting up a new [GitHub Action](https://help.github.com/en/actions), but the basic steps will require you to:
* Click on the **Actions** option at the top of your repository page.
* Click on the **New workflow** button.
* Click on the **Set up a workflow yourself** button.
* Edit the suggested page name if you want.
* Delete the default text in the edit window that will appear.
* Copy and paste the raw version of [build.yml](https://raw.githubusercontent.com/jpadfield/simple-site/master/.github/workflows/build.yml) file into the editor.
* Click on the **Start commit** button to save the new workflow.

# Dependencies

Once built, simple html pages should run without any additional software, all of the basic required [js](https://en.wikipedia.org/wiki/JavaScript) and [css](https://en.wikipedia.org/wiki/Cascading_Style_Sheets) files are already included, or externally referenced but more information about the main ones can be found at:
* [Bootstrap](https://getbootstrap.com/)
* [Jquery](https://jquery.com/)
* [Project Mirador](https://projectmirador.org/)
* [Mermaid](https://mermaid-js.github.io/mermaid)

New sets of html pages are created automatically, within GitHub, when the json files are edited, controlled by a customised [GitHub Action](https://help.github.com/en/actions), called [build.yml](.github/workflows/build.yml). However, if you want to be able to run the build process manually within a downloaded version of the repository you will also need to have **php** installed:
* [PHP](https://en.wikipedia.org/wiki/PHP)
