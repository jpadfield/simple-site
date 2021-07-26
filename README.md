[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.5137663.svg)](https://doi.org/10.5281/zenodo.5137663)

# simple-site

This is a very simple set of processes for creating a standard set of webpages based on a simple set of json files. This project is intended to work along side other projects to provide a simple way of creating a set of consistent webpages, which can be delivered as part of your own GitHub project using [GitHub pages](https://pages.github.com/).

### An example set of pages, including more detailed instructions and examples can be seen at: [simple-site](https://jpadfield.github.io/simple-site/)

### For those new to GitHub there is a lot of general documentation out there, such as [Github Guides](https://guides.github.com/) and [Getting Started with GitHub](https://help.github.com/en/github/getting-started-with-github), but one particular place to start might be the series of videos [GitHub for Poets](https://www.youtube.com/playlist?list=PLRqwX-V7Uu6ZF9C0YMKuns9sLDzK6zoiV).

The content of the pages are controlled with two main [json](https://en.wikipedia.org/wiki/JSON) files stored in the **build** folder, further details are supplied within the [simple-site](https://jpadfield.github.io/simple-site/) web pages:

* Various site wide details are defined within the [site.json](./build/site.json) file.
* The content for the webpages are then generally defined within the [pages.json](./build/pages.json) file, this includes the main pages which will be listed as tabs and then any required sub-pages, or even sub-sub-pages etc, that will be listed within drop-down menus from a related main page tab.

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
* A copy of the **graphics** folder, to house any required images.
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

# Enabling GitHub Pages
GitHub does not present project web-pages, GitHub Pages, by default, but it is very easy to turn this functionality on. The following steps can be followed when using this project, for more general instructions, or if you want to find more information you should be able to start [here](https://pages.github.com/)

* This process assumes you have copied the required project files, specifically the **docs** folder.
* Start from your project landing page - for the simple-site project the landing page is https://github.com/jpadfield/simple-site/.
* Click on the **Settings** tab, which should be towards the upper right corner of the page.
* Scroll down until you reach the **GitHub Pages** section.
* Select the **master branch /docs folder** option in the **Source** dropdown.
* There will be a slight pause and the screen should be updated, with an indication of what your new web-page address will be.
* Your GitHub Pages should now be up and running.

# Dependencies

Once built, simple html pages should run without any additional software, all of the basic required [js](https://en.wikipedia.org/wiki/JavaScript) and [css](https://en.wikipedia.org/wiki/Cascading_Style_Sheets) files are already included, or externally referenced but more information about the main ones can be found at:
* [Bootstrap](https://getbootstrap.com/)
* [Jquery](https://jquery.com/)
* [Project Mirador](https://projectmirador.org/)
* [Mermaid](https://mermaid-js.github.io/mermaid)

New sets of html pages are created automatically, within GitHub, when the json files are edited, controlled by a customised [GitHub Action](https://help.github.com/en/actions), called [build.yml](.github/workflows/build.yml). However, if you want to be able to run the build process manually within a downloaded version of the repository you will also need to have **php** installed:
* [PHP](https://en.wikipedia.org/wiki/PHP)


# Acknowledgement
This project was developed and tested as part of the work of several projects:

## H2020 EU project [SSHOC](https://sshopencloud.eu/)
<img height="64px" src="https://github.com/jpadfield/simple-site/blob/master/docs/graphics/sshoc-logo.png" alt="SSHOC Grant Info">
<img height="32px" src="https://github.com/jpadfield/simple-site/blob/master/docs/graphics/sshoc-eu-tag2.png" alt="SSHOC Grant Info">

## The EPSRC Funded [ARTICT](https://research.ng-london.org.uk/external/ARTICT) project
<img  height="64px" src="https://github.com/jpadfield/simple-site/blob/master/docs/graphics/UKRI_EPSR_Council-Logo_Horiz-RGB.png" alt="EPSRC Logo">

## The AHRC Funded [IIIF - TANC](https://tanc-ahrc.github.io/IIIF-TNC) project
<img height="64px" src="https://github.com/jpadfield/simple-site/blob/master/docs/graphics/TANC - IIIF.png" alt="IIIF - TNC">

## The AHRC Funded [HeritagePIDs](https://tanc-ahrc.github.io/HeritagePIDs) project
<img height="64px" src="https://github.com/jpadfield/simple-site/blob/master/docs/graphics/TANC - PIDS.png" alt="HeritagePIDs">
