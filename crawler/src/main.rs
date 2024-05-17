use select::{document::Document, predicate::{Attr, Class, Name, Predicate}};

fn main() -> Result<(), Box<dyn std::error::Error>> {
    crawl_featured()
}

fn crawl_featured() -> Result<(), Box<dyn std::error::Error>> {
    let client = reqwest::blocking::Client::new();
    let res = client.get("https://www.visithaarlem.com/uitagenda/").send()?.error_for_status()?;
    let body = res.text()?;
    let document = Document::from(body.as_str());
    let posts = document.find(Class("jet-listing-grid__slider").descendant(Attr("data-post-id", ())));
    for post in posts {
        // println!("{:?}", post);
        let post_id = post.attr("data-post-id").unwrap();
        let url = post.find(Attr("data-url", ())).next().unwrap().attr("data-url").unwrap();
        let mut headings = post.find(Name("h2")).map(|e| e.text());
        let title = headings.next().unwrap();
        let date = headings.next().unwrap();
        println!("{} {} {} {}", post_id, url, title, date);
    }
    Ok(())
}
