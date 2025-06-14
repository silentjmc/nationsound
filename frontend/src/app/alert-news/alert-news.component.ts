import { Component} from '@angular/core';
import { CommonModule } from '@angular/common';
import { NotificationService } from '../services/notification.service';
import { News } from '../services/class';

@Component({
  selector: 'app-alert-news',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './alert-news.component.html',
  styleUrl: './alert-news.component.css'
})

export class AlertNewsComponent {
  notification$ = this.notificationService.getCurrentNotification();
  readonly MAX_CONTENT_LENGTH = 150;

  constructor(private notificationService: NotificationService) {}
      getNotificationClass(notification: News): string {
        return `alert-${notification.typeNews}`; 
    }

    dismissNotification(notification: News) {
      this.notificationService.dismissNotification(notification.idNews);
    }

    getAlertClass(type: string): string {
      if (!type) return '';
  
      const baseClasses = 'bg-opacity-90 border-2';
      switch (type) {
        case 'primary': return `${baseClasses} bg-blue-50 border-blue-500 text-blue-700`;
        case 'warning': return `${baseClasses} bg-yellow-50 border-yellow-500 text-yellow-700`;
        case 'danger': return `${baseClasses} bg-red-50 border-red-500 text-red-700`;
        default: return `${baseClasses} bg-gray-50 border-gray-500 text-gray-700`;
      }
    }

  isContentTruncated(notification: News): boolean {
    return notification?.contentNews?.length > this.MAX_CONTENT_LENGTH;
  }

  getTruncatedContent(notification: News): string {
    if (!notification?.contentNews) return '';
    if (notification.contentNews.length <= this.MAX_CONTENT_LENGTH) {
      return notification.contentNews;
    }
    return `${notification.contentNews.slice(0, this.MAX_CONTENT_LENGTH)}...`;
  }

  goToNewsDetail(notification: News) {
    this.dismissNotification(notification);
    window.location.href = `/informations/actualite/${notification.idNews}`;
  }
}