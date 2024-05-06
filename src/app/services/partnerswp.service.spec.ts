import { TestBed } from '@angular/core/testing';

import { PartnerswpService } from './partnerswp.service';

describe('PartnerswpService', () => {
  let service: PartnerswpService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PartnerswpService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
