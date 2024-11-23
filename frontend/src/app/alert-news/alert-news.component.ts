import { Component, OnInit, PLATFORM_ID , Inject } from '@angular/core';
import Pushy from 'pushy-sdk-web';
import 'babel-polyfill';
import { CommonModule, isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-alert-news',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './alert-news.component.html',
  styleUrl: './alert-news.component.css'
})
export class AlertNewsComponent implements OnInit {
  constructor(@Inject(PLATFORM_ID) private platformId: Object) {}

  async ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      // Import Pushy dynamiquement seulement côté client
      try {
        const Pushy = (await import('pushy-sdk-web')).default;
        
        Pushy.register({ appId: '6738f360377b179337a402cb' })
          .then((deviceToken) => {
            console.log('Pushy device token: ' + deviceToken);
            alert('Pushy device token: ' + deviceToken);

            // Check if the user is registered
            if (Pushy.isRegistered()) {
              // Subscribe the user to a topic
              Pushy.subscribe('news')
              .then(() => {
                // Notify user of success
                console.log('Successfully subscribed to the topic "news"');
                alert('Successfully subscribed to the topic "news"');
              })
              .catch(function (err) {
                  // Notify user of failure
                  alert('Subscribe failed: ' + err.message);
              });
            }
          })
          .catch((err) => {
            console.error('Registration failed:', err);
            alert('Registration failed: ' + err.message);
          });
      } catch (error) {
        console.error('Error loading Pushy:', error);
      }
    }
  }
} 
  

