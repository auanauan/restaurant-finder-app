<template>
  <div class="container mt-4" style="min-height: 100vh; padding-bottom: 50px;">
    <h2 class="mb-3 text-primary">🍽️ Restaurant Finder</h2>

    <!-- Search Box -->
    <div class="input-group mb-3">
      <input v-model="keyword" @keyup.enter="search" placeholder="Enter location..."
        class="form-control border rounded-start" />
      <button @click="search" class="btn btn-primary rounded-end">Search</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="mb-4 text-muted">Loading...</div>

    <!-- Map and Cards -->
    <div v-if="restaurants.length">
      <!-- Google Map -->
      <div id="map" style="height: 400px;" class="mb-4 rounded shadow border"></div>

      <!-- Restaurant Cards -->
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <div class="col" v-for="r in restaurants" :key="r.place_id">
          <div class="card h-100 shadow border-0 rounded-4">
            <div class="card-body">
              <h5 class="card-title text-primary">{{ r.name }}</h5>
              <p class="card-text">
                {{ r.formatted_address }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const keyword = ref('Bang Sue')
const restaurants = ref([])
const loading = ref(false)

const search = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://localhost:8000/api/search', {
      params: { keyword: keyword.value }
    })
    console.log(res.data)
    restaurants.value = res.data.results
    initMap()
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const initMap = () => {
  const script = document.createElement('script')
  const apiKey = '' // เพิ่ม google credential api key ก่อนนะครับ
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMapScript`
  script.async = true
  document.head.appendChild(script)

  window.initMapScript = () => {
    const map = new google.maps.Map(document.getElementById('map'), {
      zoom: 13,
      center: restaurants.value[0]?.geometry?.location || { lat: 13.800, lng: 100.520 }
    })

    const infoWindow = new google.maps.InfoWindow()

    restaurants.value.forEach(place => {
      const marker = new google.maps.Marker({
        map,
        position: place.geometry.location,
        title: place.name
      })

      // แสดงรายละเอียดเมื่อคลิก Pin
      marker.addListener('click', () => {
        const photoRef = place.photos?.[0]?.photo_reference
        const photoUrl = photoRef
          ? `https://maps.googleapis.com/maps/api/place/photo?maxwidth=300&photoreference=${photoRef}&key=${apiKey}`
          : null

        const content = `
          <div style="max-width: 250px;">
              <h5 style="margin:0; color:#007bff;">${place.name}</h5>
              ${photoUrl ? `<img src="${photoUrl}" alt="${place.name}" style="width:100%; border-radius: 8px; margin: 8px 0;" />` : ''}
              <p style="margin:0;">${place.formatted_address}</p>
          </div> `
        infoWindow.setContent(content)
        infoWindow.open(map, marker)
      })
    })
  }
}

onMounted(() => {
  search()
})
</script>
