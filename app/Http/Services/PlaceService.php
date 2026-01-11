<?php

namespace App\Http\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Storage;

class PlaceService
{
    /**
     * Static place data
     */
    private const PLACES = [
        1 => [
            'id' => 1,
            'name' => 'Tulaan Beach Resort',
            'type' => 'Resort',
            'location' => 'Barangay Bacong, Babatngon, Leyte',
            'description' => 'A pristine coastal paradise offering crystal-clear waters and serene natural beauty. Perfect for families and groups looking to unwind by the sea with modern amenities and traditional Filipino hospitality.',
            'latitude' => 11.402741,
            'longitude' => 124.808230,
            'images' => [
                'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
                'https://cdns.app/wgsdkw2F/assets/image/properties/6fc242df6575817414af45ade9869cda_1660367236.jpg',
                'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
                'https://tse3.mm.bing.net/th/id/OIP.D3amHBu0k4VbyKfVB3hq-AHaFj?pid=Api&P=0&h=220',
                'https://tse2.mm.bing.net/th/id/OIP.g-9AsnryCzjVyl8dNNIK_wHaEK?pid=Api&P=0&h=220',
                'https://tse2.mm.bing.net/th/id/OIP.56_aXi8YhJQtPf1j56TvHgHaFi?pid=Api&P=0&h=220',
            ],
            'entrance_fee' => '₱100 per person',
            'opening_hours' => '7:00 AM - 6:00 PM',
            'best_for' => ['Family', 'Relaxation', 'Swimming'],
            'facilities' => ['Swimming Pool', 'Overnight Rooms', 'Cottages', 'Restaurant', 'Parking', 'Restrooms'],
        ],
        2 => [
            'id' => 2,
            'name' => 'Balay ni Tatay',
            'type' => 'Resort',
            'location' => 'Barangay Villa Magsaysay, Babatngon',
            'description' => 'Mountain-side resort with a pool, mini zoo, and hiking trails. A perfect destination for families seeking adventure and nature activities in a serene mountain setting.',
            'latitude' => 11.3515432,
            'longitude' => 124.9133535,
            'images' => [
                'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay-resort-1-768x434.jpg',
                'https://img.cocotel.com/frontend/hotels/195/balay-ni-tatay-farm-resort-g1881.jpg',
                'https://3.bp.blogspot.com/-AaElw8KZR2Y/W4SkEOhXTYI/AAAAAAAADCg/bDBr_VaD6xI49k5SUn2FKJ963gBgW6FlQCLcBGAs/s1600/LRM_EXPORT_20180828_083445.jpg',
                'https://4.bp.blogspot.com/--37btnNDiIk/W4SjtBv5GzI/AAAAAAAADBs/heVUuYW-WtY7NGYC-pb-GF01vRAWFhF_ACLcBGAs/s1600/IMG_20180828_080637.jpg',
                'https://cebulandscaping.com/wp-content/uploads/2021/09/66493830_1575126529284775_5896735912653488128_n.jpg',
                'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay7-1-610x458.jpg',
            ],
            'entrance_fee' => '₱150 per person',
            'opening_hours' => '8:00 AM - 7:00 PM',
            'best_for' => ['Family', 'Adventure', 'Nature'],
            'facilities' => ['Swimming Pool', 'Mini Zoo', 'Hiking Trails', 'Restaurant', 'Parking', 'Picnic Areas'],
        ],
        3 => [
            'id' => 3,
            'name' => 'Busay Falls',
            'type' => 'Falls',
            'location' => 'Barangay District III, Babatngon',
            'description' => 'Fresh waterfall flowing directly from the mountain, ideal for nature lovers. Experience the refreshing cascade of crystal-clear mountain water in a pristine natural environment perfect for swimming and relaxation.',
            'latitude' => 11.4077345,
            'longitude' => 124.8411038,
            'images' => [
                'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
                'https://media-cdn.tripadvisor.com/media/photo-s/03/02/08/d5/busay-falls.jpg',
                'https://scontent.fmnl4-8.fna.fbcdn.net/v/t39.30808-6/494899259_680194271484041_1610373663631108083_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=833d8c&_nc_eui2=AeGGalUxo0dz0HhfhvE5QgVLhjT2qepivFCGNPap6mK8UGM7Jo1hlDX5IvvZ_YIdEgSy-FgisMhaOwsrxjebiMPy&_nc_ohc=i8exqn1kmFIQ7kNvwHe8pUI&_nc_oc=AdnPul01IyAOooK7hUJHqz9y3XBlvFdYeVuDJ7WPhTR9iWuJBbQLKlzhz8odUYqtC7s&_nc_zt=23&_nc_ht=scontent.fmnl4-8.fna&_nc_gid=R4r_7lfD3tPPn1AWGnXSgA&oh=00_AfoC7kaRsKuqjzOUbxDe2cXl9fplu92rmPJUwGIKavjU1Q&oe=695C0FC9',
            ],
            'entrance_fee' => '₱50 per person',
            'opening_hours' => '6:00 AM - 5:00 PM',
            'best_for' => ['Nature', 'Swimming', 'Photography'],
            'facilities' => ['Changing Rooms', 'Parking', 'Cottages', 'Restrooms', 'Viewing Deck'],
        ],
        4 => [
            'id' => 4,
            'name' => 'Aplaya Beach',
            'type' => 'Beach',
            'location' => 'Fishport, Babatngon',
            'description' => 'Rocky sea area near the highway, perfect for quick visits and photos. A scenic coastal spot ideal for sunset viewing and capturing memorable photographs of the rugged coastline.',
            'latitude' => 11.4245752,
            'longitude' => 124.832663,
            'images' => [
                'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
                'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
            ],
            'entrance_fee' => 'Free',
            'opening_hours' => 'Open 24/7',
            'best_for' => ['Photography', 'Sunset', 'Quick Visit'],
            'facilities' => ['Parking', 'Viewing Areas', 'Food Stalls Nearby', 'Cottages'],
        ],
        5 => [
            'id' => 5,
            'name' => 'Tulaan Beach',
            'type' => 'Beach',
            'location' => 'Barangay Bacong, Babatngon',
            'description' => 'Part of Tulaan Resort, with clear seawater and a relaxing beach environment. Enjoy pristine white sand beaches and turquoise waters perfect for swimming, snorkeling, and beach relaxation.',
            'latitude' => 11.402741,
            'longitude' => 124.808230,
            'images' => [
                'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
                'https://cdns.app/wgsdkw2F/assets/image/properties/6fc242df6575817414af45ade9869cda_1660367236.jpg',
                'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
                'https://tse3.mm.bing.net/th/id/OIP.D3amHBu0k4VbyKfVB3hq-AHaFj?pid=Api&P=0&h=220',
                'https://tse2.mm.bing.net/th/id/OIP.g-9AsnryCzjVyl8dNNIK_wHaEK?pid=Api&P=0&h=220',
                'https://tse2.mm.bing.net/th/id/OIP.56_aXi8YhJQtPf1j56TvHgHaFi?pid=Api&P=0&h=220',
            ],
            'entrance_fee' => '₱100 per person',
            'opening_hours' => '7:00 AM - 6:00 PM',
            'best_for' => ['Family', 'Relaxation', 'Swimming'],
            'facilities' => ['Swimming Pool', 'Overnight Rooms', 'Cottages', 'Restaurant', 'Parking', 'Restrooms'],
        ],
        6 => [
            'id' => 6,
            'name' => 'Busay Resort',
            'type' => 'Resort',
            'location' => 'Barangay District III, Babatngon',
            'description' => 'Resort with a pool, waterfall, and slide for fun activities. A family-friendly destination combining natural waterfalls with modern resort amenities for an unforgettable experience.',
            'latitude' => 11.4077345,
            'longitude' => 124.8411038,
            'images' => [
                'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
                'https://media-cdn.tripadvisor.com/media/photo-s/03/02/08/d5/busay-falls.jpg',
            ],
            'entrance_fee' => '₱50 per person',
            'opening_hours' => '6:00 AM - 5:00 PM',
            'best_for' => ['Nature', 'Swimming', 'Photography'],
            'facilities' => ['Changing Rooms', 'Parking', 'Cottages', 'Restrooms', 'Viewing Deck'],
        ],
    ];

    /**
     * Get a place by ID
     */
    public function getPlaceById(int $id): ?array
    {
        return self::PLACES[$id] ?? null;
    }

    /**
     * Get all places with dynamic ratings
     */
    public function getAllPlacesWithRatings(): array
    {
        $simplePlaces = [
            [
                'id' => 1,
                'name' => 'Tulaan Beach Resort',
                'description' => 'Clean environment with rooms for overnight stay and a swimming pool.',
                'location' => 'Barangay Bacong, Babatngon',
                'image' => 'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
                'type' => 'Resort',
            ],
            [
                'id' => 2,
                'name' => 'Balay ni Tatay',
                'description' => 'Mountain-side resort with a pool, mini zoo, and hiking trails.',
                'location' => 'Barangay Villa Magsaysay, Babatngon',
                'image' => 'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay-resort-1-768x434.jpg',
                'type' => 'Resort',
            ],
            [
                'id' => 3,
                'name' => 'Busay Falls',
                'description' => 'Fresh waterfall flowing directly from the mountain, ideal for nature lovers.',
                'location' => 'Barangay District III, Babatngon',
                'image' => 'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
                'type' => 'Falls',
            ],
            [
                'id' => 4,
                'name' => 'Aplaya Beach',
                'description' => 'Rocky sea area near the highway, perfect for quick visits and photos.',
                'location' => 'Fishport, Babatngon',
                'image' => 'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
                'type' => 'Beach',
            ],
            [
                'id' => 5,
                'name' => 'Tulaan Beach',
                'description' => 'Part of Tulaan Resort, with clear seawater and a relaxing beach environment.',
                'location' => 'Barangay Bacong, Babatngon',
                'image' => 'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
                'type' => 'Beach',
            ],
            [
                'id' => 6,
                'name' => 'Busay Resort',
                'description' => 'Resort with a pool, waterfall, and slide for fun activities.',
                'location' => 'Barangay District III, Babatngon',
                'image' => 'https://media-cdn.tripadvisor.com/media/photo-s/03/02/08/d5/busay-falls.jpg',
                'type' => 'Resort',
            ],
        ];

        // Get all review stats from database in one query
        $reviewStats = Review::selectRaw('place_id, AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->groupBy('place_id')
            ->get()
            ->keyBy('place_id');

        // Merge static place data with dynamic ratings
        return array_map(function($place) use ($reviewStats) {
            $stats = $reviewStats->get($place['id']);
            
            $place['rating'] = $stats ? round($stats->avg_rating, 1) : 0;
            $place['reviewCount'] = $stats ? $stats->review_count : 0;
            
            return $place;
        }, $simplePlaces);
    }

    /**
     * Get place with ratings
     */
    public function getPlaceWithRatings(int $id): ?array
    {
        $place = $this->getPlaceById($id);
        
        if (!$place) {
            return null;
        }

        // Calculate average rating and review count from database
        $reviewStats = Review::where('place_id', $id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        $place['rating'] = $reviewStats->avg_rating ? round($reviewStats->avg_rating, 1) : 0;
        $place['review_count'] = $reviewStats->review_count ?? 0;

        return $place;
    }

    /**
     * Get formatted reviews for a place
     */
    public function getFormattedReviews(int $placeId, ?int $userId = null): array
    {
        return Review::where('place_id', $placeId)
            ->with('user:id,name')
            ->withCount('reports')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) use ($userId) {
                return [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'user_name' => $review->user->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'photos' => $review->photos ? array_map(function($photo) {
                        return Storage::url($photo);
                    }, $review->photos) : [],
                    'created_at' => $review->created_at->toISOString(),
                    'reports_count' => $review->reports_count,
                    'has_reported' => $userId ? $this->hasUserReportedReview($review->id, $userId) : false,
                ];
            })->toArray();
    }

    /**
     * Check if user has reported a review
     */
    private function hasUserReportedReview(int $reviewId, int $userId): bool
    {
        return \App\Models\ReviewReport::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->exists();
    }

    
}
