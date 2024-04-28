export interface WPCategory {
  termId: number;
  slug: string;
  name: string;
  url: string;
  thumbnail: string;
  parsedName: string;
}

export interface DeviceTypeCategory extends WPCategory {
  termId: number;
  slug: string;
  name: string;
  brands: DeviceBrandCategory[];
}

export interface DeviceBrandCategory extends WPCategory {
  termId: number;
  slug: string;
  name: string;
  models: WPCategory[];
}
