<?php

namespace App\Services\Settings\Master;

use App\Repositories\Settings\Master\KetegoriUtamaRepository;

class KategoriUtamaService
{
    protected $KetegoriUtamaRepository;

    public function __construct(KetegoriUtamaRepository $KetegoriUtamaRepository)
    {
        $this->KetegoriUtamaRepository = $KetegoriUtamaRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getKategoriUtama()
    {
        $dataKategoriUtama = $this->KetegoriUtamaRepository->getKategoriUtama();
        return $dataKategoriUtama;
    }

    public function getKategoriUtamaTable()
    {
        $KategoriUtama = $this->KetegoriUtamaRepository->getKategoriUtama();

        $dataKategoriUtama = [];
        foreach ($KategoriUtama as $r) {
            $dataKategoriUtama[] = [
                'id'        => $r->id,
                'name'      => $r->name,
                'code'      => $r->code,
            ];
        }

        return $dataKategoriUtama;
    }

    public function createKategoriUtama(array $data)
    {
        $dataKategoriUtama = $this->KetegoriUtamaRepository->createKategoriUtama($data);
        return $dataKategoriUtama;
    }

    public function findByIdKategoriUtama($id)
    {
        $KategoriUtama = $this->KetegoriUtamaRepository->findByIdKategoriUtama($id);
        return $KategoriUtama;
    }

    public function updateKategoriUtama($id, array $data)
    {
        $KategoriUtama = $this->KetegoriUtamaRepository->findByIdKategoriUtama($id);
        $KategoriUtama->update($data);
        return $KategoriUtama;
    }

    public function deleteKategoriUtama($id)
    {
        return $this->KetegoriUtamaRepository->findByIdKategoriUtama($id)->delete();
    }
}
