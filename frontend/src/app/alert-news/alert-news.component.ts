import { Component, OnInit, OnDestroy } from '@angular/core';
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
  readonly MAX_CONTENT_LENGTH = 150; // Limite de caractères pour le content

  constructor(private notificationService: NotificationService) {}
      getNotificationClass(notification: News): string {
        return `alert-${notification.type}`; 
    }

    dismissNotification(notification: News) {
      this.notificationService.dismissNotification(notification.id);
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
    return notification?.content?.length > this.MAX_CONTENT_LENGTH;
  }

  getTruncatedContent(notification: News): string {
    if (!notification?.content) return '';
    if (notification.content.length <= this.MAX_CONTENT_LENGTH) {
      return notification.content;
    }
    return `${notification.content.slice(0, this.MAX_CONTENT_LENGTH)}...`;
  }

  goToNewsDetail(notification: News) {
    // Fermer la notification
    this.dismissNotification(notification);
    // Rediriger vers la page de détail
    window.location.href = `/informations/actualite/${notification.id}`;
  }
}
  

