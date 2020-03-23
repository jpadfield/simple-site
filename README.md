# simple-site

This is a very simple set processes for creating a standard set of webpages based on a simple json file. 

An example set of pages can be seen at: [simple-site](https://jpadfield.github.io/simple-site/)

The content of the pages are controlled with three [json](https://en.wikipedia.org/wiki/JSON) files stored in the build folder, further details are supplied with in the build directory [ReadMe](./build/README.md) file:

* Various site wide details are defined within the [site.json](./build/site.json) file.
* The content for the various main pages, which will be listed as tabs, are defined within the [pages.json](./build/pages.json) file.
* Any required sub-pages can then also be defined within the [sub-pages.json](./build/sub-pages.json) file. These sub-pages, or even sub-sub-pages etc, will not be given main tabs, but will be listed as buttons beneath the appropriate main page.

# Screenshots 
<img src="./docs/graphics/example screenshot 01.png" width="50%" alt="Example Screenshot"><img src="./docs/graphics/example screenshot 02.png" width="50%" alt="Example Screenshot">

Once the json files are updated, within the GitHub repository, a new set of html pages are automatically generated using the [build.php](build/build/php) file.

# Dependencies

Once built, simple html pages should run without any additional software, all of the basic required [js](https://en.wikipedia.org/wiki/JavaScript) and [css](https://en.wikipedia.org/wiki/Cascading_Style_Sheets) files are already included, but newer versions can be added in if required:
* [Bootstrap](https://getbootstrap.com/)
* [Jquery](https://jquery.com/)

New sets of html pages are created automatically, within GitHub, when the json files are edited, controlled by a customised [GitHub Action](https://help.github.com/en/actions), called [build.yml](.github/workflows/build.yml). However, if you want to be able to run the build process manually within a downloaded version of the repository you will also need to have **php** installed:
* [PHP](https://en.wikipedia.org/wiki/PHP)
