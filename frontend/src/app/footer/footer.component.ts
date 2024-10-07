import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MentionsComponent } from '../mentions/mentions.component';
import { ConfidentialiteComponent } from '../confidentialite/confidentialite.component';

@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [RouterLink, MentionsComponent, ConfidentialiteComponent],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.css'
})
export class FooterComponent  {



}
