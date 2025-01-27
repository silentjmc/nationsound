import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { RouterLink, RouterModule } from '@angular/router';
import { Observable } from 'rxjs';
import { News } from '../services/class';
import { NewsService } from '../services/news.service';

@Component({
  selector: 'app-news-summary',
  standalone: true,
  imports: [CommonModule, RouterModule, RouterLink],
  templateUrl: './news-summary.component.html',
  styleUrl: './news-summary.component.css'
})
export class NewsSummaryComponent {
  news$: Observable<News[]>;
  readonly maxLength = 150;

  constructor(private newsService: NewsService) {
    this.news$ = this.newsService.getAllNews();
  }

  ngOnInit(): void { }

  truncateText(text: string): { content: string, isTruncated: boolean } {
    if (text.length <= this.maxLength) {
      return { content: text, isTruncated: false };
    }
    return {
      content: text.substring(0, this.maxLength) + '...',
      isTruncated: true
    };
  }
}
