let apiUrl = "./pages.json"

async function getJson(url) {
    let response = await fetch(url);
    let data = await response.json()
    return data;
}

async function main() {
    // getJson(apiUrl)
    //     .then(data => console.log(data));
    let documents = await getJson(apiUrl)

    let idx = lunr(function () {
      this.field('id')
      this.field('title')
      this.field('content', { boost: 10 })
      this.field('url')
      Object.entries(documents).forEach(function (document) {
        this.add( {
          "id": document[0],
          "title": document[1]['title'],
          'content': document[1]['content'],
        })
      }, this)
    });
    let searchParams = new URLSearchParams(window.location.search)
    let param = searchParams.get('query')
    var results = idx.search(param);
    if (results.length) {
    for (result of results) {
      var doc = documents[result.ref];
      $( "#search_results" ).append(
      '<div class="col-md-6 mt-3"><div class="card h-100"><div class="card-body"><a href="'
      + doc.url + '"><h5 class="card-title">'
      + doc.title
      + '</h5></a>'
      + '<p class="card-text">' + doc.content + '</p>'
      + '<a href="' + doc.url + '" class="btn btn-dark stretched-link">Read more </a>'
      + '</div></div>');
      }
    } else {
      $( "#search_results" ).append(
        '<div class="container"><div class="col-md-12 mt-3 mb-3 shadow-sm  p-3 bg-white"><p>Your search found no results</p></div></div>'
      );
    }

}

main();
