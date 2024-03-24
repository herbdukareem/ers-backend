<?php

use App\Models\Facility;
use App\Models\LGA;
use App\Models\Ward;

$lgas  = LGA::all();
$wards = Ward::all();
$facilities = Facility::all();
?>
@extends('layouts.app')

@section('content')

<div class="w-full mt-4 " id="appRoot2">
    <div class="bg-white rounded  pb-3 border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
     <!--    <div class="p-3">
            <div class="flex flex-wrap p-3 rounded-lg card justify-between">
                <div class="w-full md:w-1/6">
                    <label class="block">Filter</label>
                    <input id="input" wire:model="dateRange" class="w-full border" autocomplete="off">
                </div>
                <div class="w-full md:w-1/6">
                    <label class="block">LGA</label>
                    <select class="w-full border" autocomplete="off" wire:model="searchLga">
                        <option value=""></option>
                        @foreach($lgas as $lga)
                        <option value="{{$lga->id}}">{{$lga->lga}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/6">
                    <label class="block">Ward</label>
                    <select class="w-full border" autocomplete="off" wire:model="searchWard">
                        <option value=""></option>
                        @foreach($wards as $ward)
                        <option value="{{$ward->id}}">{{$ward->ward}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/6">
                    <label class="block">Facility</label>
                    <select class="w-full border" autocomplete="off" wire:model="searchFacility">
                        <option value=""></option>
                        @foreach($facilities as $facility)
                        <option value="{{$facility->id}}">{{$facility->hcpname}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/6 flex items-center">
                    <button wire:click="clear" class="btn btn-light mx-2 hover:text-[skyblue]" wire:loading.attr="disabled">Clear</button>
                    <button wire:click="exportData" class="btn btn-light mx-2 hover:text-[skyblue]" wire:loading.attr="disabled">Export</button>
                </div>
            </div>
            <hr class="gradient-hr">
        </div> -->

    </div>
    
</div>

    <script>
        const {
            createApp
        } = Vue

        createApp({
            data() {
        return {
            // Define data properties to store the fetched data
            encounters: {},       
            // Additional data properties...
        }
    },
    created() {        
        this.fetchTotalEncounters();   
    },
    mounted(){
        window.addEventListener('custom-input-event', this.searchedInput);
    },
    methods: {
        searchedInput(e){
            
        },
        async fetchTotalEncounters() {
            const response = await fetch('/ecounters');
            this.encounters = await response.json();
        },      
    }
        }).mount('#appRoot2')
    </script>
    <style>
        .pagi div nav div span {
            display: flex;
        }
    </style>
@endsection