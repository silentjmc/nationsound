import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable, BehaviorSubject } from 'rxjs';
import { News } from '../services/class';
import { NewsService } from '../services/news.service';
import { CommonModule, Location } from '@angular/common';

@Component({
  selector: 'app-news-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './news-detail.component.html',
  styleUrl: './news-detail.component.css'
})
export class NewsDetailComponent {
  news$!: Observable<News | null>;
  loading$ = new BehaviorSubject<boolean>(true);
  error$ = new BehaviorSubject<boolean>(false);

  constructor(private route: ActivatedRoute,private newsService: NewsService, private location: Location)
   {}

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    if (id) {
      this.loading$.next(true);
      this.error$.next(false);
      
      this.newsService.getNewsById(id).subscribe({
        next: (news) => {
          if (news) {
            this.news$ = new Observable(observer => {
              observer.next(news);
              observer.complete();
            });
          } else {
            this.error$.next(true);
          }
          this.loading$.next(false);
        },
        error: () => {
          this.error$.next(true);
          this.loading$.next(false);
        }
      });
    } else {
      this.error$.next(true);
      this.loading$.next(false);
    }
  }

  goBack(): void {
    this.location.back();
  }
}