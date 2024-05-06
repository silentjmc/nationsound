import { HttpClient } from '@angular/common/http';
import { Component, OnInit, inject } from '@angular/core';


@Component({
  selector: 'app-information',
  standalone: true,
  imports: [],
  templateUrl: './information.component.html',
  styleUrl: './information.component.css'
})

export class InformationComponent implements OnInit {
  http = inject(HttpClient);

  ngOnInit(): void {
    this.fetchPosts();
    //Called after the constructor, initializing input properties, and the first call to ngOnChanges.
    //Add 'implements OnInit' to the class.
  }
  fetchPosts() {
    this.http.get('https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/partenaires')
    .subscribe((posts: any) =>{
      const mappedPosts = posts.map((post: any) => {
        return {
          id: post.id,
          title: post.title.rendered,
          urlPartenaire: post.acf.url_partenaire,
          logoPartenaire: post.acf.logo_partenaire,
          typePartenaire:post.acf.type_partenaire
        };
      });

      console.log(mappedPosts);
  
    })
  }

}