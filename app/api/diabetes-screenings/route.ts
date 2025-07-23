// // app/api/diabetes-screenings/route.ts
// import { NextRequest, NextResponse } from 'next/server';

// export async function GET(request: NextRequest) {
//   try {
//     // URL Laravel backend
//     const baseUrl = process.env.LARAVEL_API_URL || 'http://localhost:8000';
    
//     console.log('🔄 Fetching diabetes screenings...');
    
//     const response = await fetch(`${baseUrl}/api/diabetes-screenings`, {
//       method: 'GET',
//       headers: {
//         'Accept': 'application/json',
//         'Content-Type': 'application/json',
//       },
//     });

//     console.log(`📡 Response status: ${response.status}`);
    
//     if (!response.ok) {
//       console.error(`❌ Laravel API error: ${response.status}`);
//       return NextResponse.json([]);
//     }

//     const data = await response.json();
//     console.log(`✅ Data received:`, { count: Array.isArray(data) ? data.length : 'single item' });
    
//     return NextResponse.json(data);

//   } catch (error) {
//     console.error('❌ Next.js API error:', error);
//     return NextResponse.json([]);
//   }
// }