import { ComponentFixture, TestBed } from '@angular/core/testing';

import { NavBarInformationsComponent } from './nav-bar-informations.component';

describe('NavBarInformationsComponent', () => {
  let component: NavBarInformationsComponent;
  let fixture: ComponentFixture<NavBarInformationsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [NavBarInformationsComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(NavBarInformationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
